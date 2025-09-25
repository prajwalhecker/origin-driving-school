<?php
class DashboardController extends Controller {

  public function admin(){
    $this->requireRole('admin');
    // basic stats
    $totalStudents = $this->db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(); 
    $totalInstructors = $this->db->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn();
    $totalBookings = $this->db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $income = $this->db->query("SELECT COALESCE(SUM(amount),0) FROM payments")->fetchColumn();
    $this->view("dashboard/admin", compact('totalStudents','totalInstructors','totalBookings','income'));
  }

  public function instructor(){
    $this->requireRole('instructor');
    @session_start();
    $uid = $_SESSION['user_id'];
    // instructor’s next bookings
    $stmt = $this->db->prepare("SELECT b.*, u.first_name, u.last_name
                                FROM bookings b
                                JOIN users u ON u.id=b.student_id
                                WHERE b.instructor_id=? AND b.start_datetime >= NOW()
                                ORDER BY b.start_datetime ASC LIMIT 10");
    $stmt->execute([$uid]);
    $next = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $this->view("instructor/dashboard", ['nextBookings'=>$next]);
  }

  public function student(){
    $this->requireRole('student');
    @session_start();
    $uid = $_SESSION['user_id'];

    // Student profile details (branch, course, preferences)
    $profileStmt = $this->db->prepare("SELECT
        u.first_name,
        u.last_name,
        u.branch_id,
        b.name AS branch_name,
        sp.address,
        sp.vehicle_type,
        sp.start_date,
        sp.preferred_time,
        sp.preferred_days,
        sp.course_id,
        c.name AS course_name,
        c.price AS course_price,
        c.class_count AS course_classes,
        c.description AS course_description
      FROM users u
      LEFT JOIN student_profiles sp ON sp.user_id = u.id
      LEFT JOIN branches b ON b.id = COALESCE(sp.branch_id, u.branch_id)
      LEFT JOIN courses c ON c.id = sp.course_id
      WHERE u.id = ?");
    $profileStmt->execute([$uid]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    if (!empty($profile['branch_id'])) {
      $_SESSION['branch_id'] = (int)$profile['branch_id'];
      if (!empty($profile['branch_name'])) {
        $_SESSION['branch_name'] = $profile['branch_name'];
      }
    }

    $preferredDays = [];
    if (!empty($profile['preferred_days'])) {
      $preferredDays = array_filter(array_map('trim', explode(',', $profile['preferred_days'])));
    }

    // Upcoming lessons enriched with instructors and branch details
    $bookingStmt = $this->db->prepare("SELECT b.id, b.start_time, b.end_time, b.status,
        c.name AS course_name,
        br.name AS branch_name,
        CONCAT(inst.first_name, ' ', inst.last_name) AS instructor_name
      FROM bookings b
      LEFT JOIN courses c ON c.id = b.course_id
      LEFT JOIN branches br ON br.id = b.branch_id
      LEFT JOIN users inst ON inst.id = b.instructor_id
      WHERE b.student_id = ? AND b.start_time >= NOW()
      ORDER BY b.start_time ASC
      LIMIT 10");
    $bookingStmt->execute([$uid]);
    $upcoming = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

    // Invoice summary and outstanding balance
    $invoiceStmt = $this->db->prepare("SELECT i.*, c.name AS course_name,
        (SELECT COALESCE(SUM(amount),0) FROM payments p WHERE p.invoice_id = i.id) AS paid_total
      FROM invoices i
      LEFT JOIN courses c ON c.id = i.course_id
      WHERE i.student_id = ?
      ORDER BY i.created_at DESC
      LIMIT 5");
    $invoiceStmt->execute([$uid]);
    $invoices = $invoiceStmt->fetchAll(PDO::FETCH_ASSOC);

    $balance = 0.0;
    foreach ($invoices as &$invoice) {
      $amount = (float)($invoice['amount'] ?? 0);
      $paid = (float)($invoice['paid_total'] ?? 0);
      $invoice['balance'] = max($amount - $paid, 0);
      $balance += $invoice['balance'];
    }
    unset($invoice);

    $latestInvoice = $invoices[0] ?? null;

    $this->view("student/dashboard", [
      'profile'       => array_merge($profile, ['preferred_days_list' => $preferredDays]),
      'upcoming'      => $upcoming,
      'balance'       => $balance,
      'latestInvoice' => $latestInvoice,
    ]);
  }
}
