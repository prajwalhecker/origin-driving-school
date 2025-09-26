<?php
class DashboardController extends Controller {
    public function index() {
        @session_start();
        if (!isset($_SESSION['role'])) {
            // Guest → redirect to home page
            header("Location: index.php?url=home/index");
            exit;
        }

        $role   = $_SESSION['role'];
        $userId = $_SESSION['user_id'] ?? null;

        switch ($role) {
            case 'admin':
                $totalStudents    = $this->db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
                $totalInstructors = $this->db->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn();
                $totalBookings    = $this->db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
                $income           = $this->db->query("SELECT COALESCE(SUM(amount),0) FROM payments")->fetchColumn();

                $this->view("dashboard/index", compact(
                    'role','totalStudents','totalInstructors','totalBookings','income'
                ));
                break;

            case 'student':
    // count lessons (bookings)
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE student_id=?");
    $stmt->execute([$userId]);
    $lessonCount = $stmt->fetchColumn();

    // count enrollments (via invoices)
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM invoices WHERE student_id=?");
    $stmt->execute([$userId]);
    $enrollmentCount = $stmt->fetchColumn();

    // outstanding balance
    $stmt = $this->db->prepare("
        SELECT COALESCE(SUM(i.amount),0) - COALESCE(SUM(p.amount),0) AS balance
        FROM invoices i
        LEFT JOIN payments p ON p.invoice_id=i.id
        WHERE i.student_id=?");
    $stmt->execute([$userId]);
    $balance = $stmt->fetchColumn();

    $this->view("dashboard/index", compact(
        'role','lessonCount','enrollmentCount','balance'
    ));
    break;


            case 'instructor':
                $stmt = $this->db->prepare("SELECT COUNT(DISTINCT student_id) FROM bookings WHERE instructor_id=?");
                $stmt->execute([$userId]);
                $studentsTaught = $stmt->fetchColumn();

                $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE instructor_id=? AND status='completed'");
                $stmt->execute([$userId]);
                $completedLessons = $stmt->fetchColumn();

                $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE instructor_id=? AND start_time >= NOW()");
                $stmt->execute([$userId]);
                $upcomingLessons = $stmt->fetchColumn();

                $this->view("dashboard/index", compact(
                    'role','studentsTaught','completedLessons','upcomingLessons'
                ));
                break;
        }
    }
}
