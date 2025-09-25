<?php
class StudentController extends Controller {

  // ============================
  // LIST & PROFILE
  // ============================

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

  // ============================
  // CREATE & STORE
  // ============================

  public function create() {
    $this->requireRole('admin');

    if (function_exists('session_start_safe')) {
      session_start_safe();
    }

    $form = [];
    if (!empty($_SESSION['_old_input']) && is_array($_SESSION['_old_input'])) {
      $form = $_SESSION['_old_input'];
    }

    if (function_exists('clear_old_input')) {
      clear_old_input();
    }

    $this->view('student/create', [
      'form' => array_merge([
        'first_name' => '',
        'last_name'  => '',
        'email'      => '',
        'phone'      => '',
        'password'   => '',
      ], $form)
    ]);
  }

  public function store() {
    $this->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('index.php?url=student/create');
    }

    if (function_exists('csrf_check') && !csrf_check()) {
      if (function_exists('remember_old_input')) {
        remember_old_input();
      }
      $this->flash('flash_error', 'Security token mismatch. Please try again.');
      $this->redirect('index.php?url=student/create');
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = (string)($_POST['password'] ?? '');

    $errors = [];

    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'A valid email address is required.';
    }

    if ($email !== '') {
      $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
      $stmt->execute([$email]);
      if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $errors[] = 'Email address is already in use.';
      }
    }

    if (!empty($errors)) {
      if (function_exists('remember_old_input')) {
        remember_old_input();
      }
      $this->flash('flash_error', implode(' ', $errors));
      $this->redirect('index.php?url=student/create');
    }

    $generatedPassword = null;
    if ($password === '') {
      $generatedPassword = bin2hex(random_bytes(4));
      $password = $generatedPassword;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $this->db->prepare(
      "INSERT INTO users (branch_id, first_name, last_name, email, phone, password, role, created_at, updated_at)
       VALUES (NULL, ?, ?, ?, ?, ?, 'student', NOW(), NOW())"
    );
    $insert->execute([
      $firstName,
      $lastName,
      $email,
      $phone !== '' ? $phone : null,
      $hash,
    ]);

    $userId = (int)$this->db->lastInsertId();

    if ($userId > 0) {
      $studentStmt = $this->db->prepare("INSERT INTO students (user_id) VALUES (?)");
      $studentStmt->execute([$userId]);
    }

    if (function_exists('clear_old_input')) {
      clear_old_input();
    }

    $message = 'Student created successfully.';
    if ($generatedPassword) {
      $message .= ' Temporary password: ' . $generatedPassword;
    }

    $this->flash('flash_success', $message);
    $this->redirect('index.php?url=student/index');
  }

  // ============================
  // EDIT & UPDATE
  // ============================

  public function edit($id = null) {
    $this->requireRole('admin');

    $id = $id ?? (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, phone FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    if (function_exists('session_start_safe')) {
      session_start_safe();
    }
    $form = [];
    if (!empty($_SESSION['_old_input']) && is_array($_SESSION['_old_input'])) {
      $form = $_SESSION['_old_input'];
    }
    if (function_exists('clear_old_input')) {
      clear_old_input();
    }

    $this->view('student/edit', [
      'student' => $student,
      'form'    => $form,
    ]);
  }

  public function update($id = null) {
    $this->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('index.php?url=student/index');
    }

    $targetId = $id ?? (int)($_GET['id'] ?? 0);

    if (function_exists('csrf_check') && !csrf_check()) {
      if (function_exists('remember_old_input')) {
        remember_old_input();
      }
      $this->flash('flash_error', 'Security token mismatch. Please try again.');
      $this->redirect('index.php?url=student/edit&id=' . urlencode((string)$targetId));
    }

    $id = $targetId;
    if ($id <= 0) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    $errors = [];
    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'A valid email address is required.';
    }

    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
      $errors[] = 'Email address is already in use.';
    }

    if (!empty($errors)) {
      if (function_exists('remember_old_input')) {
        remember_old_input();
      }
      $this->flash('flash_error', implode(' ', $errors));
      $this->redirect('index.php?url=student/edit&id=' . urlencode((string)$id));
    }

    $update = $this->db->prepare(
      "UPDATE users
       SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW()
       WHERE id = ? AND role = 'student'"
    );
    $update->execute([
      $firstName,
      $lastName,
      $email,
      $phone !== '' ? $phone : null,
      $id,
    ]);

    if (function_exists('clear_old_input')) {
      clear_old_input();
    }

    $this->flash('flash_success', 'Student details updated.');
    $this->redirect('index.php?url=student/index');
  }

  // ============================
  // SHOW
  // ============================

  public function show($id = null) {
    $this->requireRole('admin');

    $id = $id ?? (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, phone FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $this->view('student/show', compact('student'));
  }

}
