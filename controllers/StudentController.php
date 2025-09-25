<?php
class StudentController extends Controller {

  public function index(){ // admin only: list students
    $this->requireRole('admin');

    $search = trim($_GET['q'] ?? '');
    $branchFilter = $_GET['branch'] ?? 'all';

    $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.created_at, b.name AS branch_name
            FROM users u
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.role='student'";
    $params = [];

    if ($branchFilter !== 'all' && $branchFilter !== '') {
      $sql .= " AND u.branch_id = ?";
      $params[] = (int)$branchFilter;
    }

    if ($search !== '') {
      $sql .= " AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
      $like = "%$search%";
      $params[] = $like;
      $params[] = $like;
      $params[] = $like;
    }

    $sql .= " ORDER BY u.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $branchStmt = $this->db->query("SELECT id, name FROM branches ORDER BY name");
    $branches = $branchStmt->fetchAll(PDO::FETCH_ASSOC);

    $totalStudents = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    $recentStudents = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role='student' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    $profileCount = (int)$this->db->query("SELECT COUNT(*) FROM student_profiles")->fetchColumn();
    $contactable = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role='student' AND phone IS NOT NULL AND phone <> ''")->fetchColumn();

    $summary = [
      'total'       => $totalStudents,
      'recent'      => $recentStudents,
      'profiles'    => $profileCount,
      'contactable' => $contactable,
    ];

    $filters = [
      'q'      => $search,
      'branch' => $branchFilter,
    ];

    $this->view("student/index", compact('students', 'branches', 'summary', 'filters'));
  }

  public function profile() {
    $this->requireRole(['student']);

    $userId = $_SESSION['user_id'];

    $stmt = $this->db->prepare("
      SELECT 
          u.first_name,
          u.last_name,
          u.email,
          u.phone,
          s.address,
          s.vehicle_type,
          c.name AS course
      FROM users u
      LEFT JOIN student_profiles s ON u.id = s.user_id
      LEFT JOIN courses c ON s.course_id = c.id
      WHERE u.id = ?
    ");

    $stmt->execute([$userId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
      $this->flash('flash_error','Profile not found.');
      $this->redirect('index.php?url=dashboard');
    }

    $this->view("student/profile", compact('student'));
  }
}
