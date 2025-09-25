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
    // student’s upcoming bookings & balances
    $stmt = $this->db->prepare("SELECT * FROM bookings WHERE student_id=? AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 10");
    $stmt->execute([$uid]);
    $next = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $due = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE student_id=? AND status IN ('unpaid','overdue')");
    $due->execute([$uid]);
    $balance = $due->fetchColumn();

    $this->view("student/dashboard", compact('next','balance'));
  }
}
