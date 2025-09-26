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
 public function enrollment() {
    $this->requireRole('student');

    $userId = $_SESSION['user_id'];

    $profileStmt = $this->db->prepare("SELECT
        u.first_name,
        u.last_name,
        u.email,
        u.branch_id AS user_branch_id,
        sp.branch_id,
        sp.course_id,
        sp.start_date,
        sp.preferred_time,
        sp.preferred_days,
        sp.vehicle_type,
        b.name AS branch_name,
        c.name AS course_name,
        c.price AS course_price
      FROM users u
      LEFT JOIN student_profiles sp ON sp.user_id = u.id
      LEFT JOIN branches b ON b.id = COALESCE(sp.branch_id, u.branch_id)
      LEFT JOIN courses c ON c.id = sp.course_id
      WHERE u.id = ?");
    $profileStmt->execute([$userId]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $branches = $this->db->query("SELECT id, name FROM branches ORDER BY name")
      ->fetchAll(PDO::FETCH_ASSOC);
    $courses = $this->db->query("SELECT id, name, price, class_count, description FROM courses ORDER BY name")
      ->fetchAll(PDO::FETCH_ASSOC);

    $preferredDays = [];
    if (!empty($profile['preferred_days'])) {
      $preferredDays = array_filter(array_map('trim', explode(',', $profile['preferred_days'])));
    }

    $selectedCourseId = null;
    if (isset($_GET['course'])) {
      $selectedCourseId = (int)$_GET['course'];
    }

    $selectedBranchId = null;
    if (isset($_GET['branch'])) {
      $selectedBranchId = (int)$_GET['branch'];
    }

    $this->view('student/enrollment', [
      'profile'       => $profile,
      'branches'      => $branches,
      'courses'       => $courses,
      'preferredDays' => $preferredDays,
      'selectedCourseId' => $selectedCourseId,
      'selectedBranchId' => $selectedBranchId,
    ]);
  }

  public function enroll() {
    $this->requireRole('student');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('index.php?url=student/enrollment');
    }

    if (function_exists('csrf_check') && !csrf_check()) {
      $this->flash('flash_error', 'Security token mismatch. Please try again.');
      $this->redirect('index.php?url=student/enrollment');
    }

    $userId = $_SESSION['user_id'];

    $branchId = (int)($_POST['branch_id'] ?? 0);
    $courseId = (int)($_POST['course_id'] ?? 0);
    $startDateInput = trim($_POST['start_date'] ?? '');
    $preferredTime = trim($_POST['preferred_time'] ?? '');
    $preferredDays = (array)($_POST['preferred_days'] ?? []);
    $vehicleType = trim($_POST['vehicle_type'] ?? '');

    if ($branchId <= 0 || $courseId <= 0) {
      $this->flash('flash_error', 'Please choose both a branch and a course.');
      $this->redirect('index.php?url=student/enrollment');
    }

    $courseStmt = $this->db->prepare("SELECT id, name, price FROM courses WHERE id = ? LIMIT 1");
    $courseStmt->execute([$courseId]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
      $this->flash('flash_error', 'Selected course could not be found.');
      $this->redirect('index.php?url=student/enrollment');
    }

    $startDateValue = null;
    if ($startDateInput !== '') {
      $startObj = DateTimeImmutable::createFromFormat('Y-m-d', $startDateInput);
      if (!$startObj) {
        $this->flash('flash_error', 'Start date must be in YYYY-MM-DD format.');
        $this->redirect('index.php?url=student/enrollment');
      }
      $startDateValue = $startObj->format('Y-m-d');
    }

    $preferredDaysValue = implode(',', array_map('trim', $preferredDays));

    try {
      $this->db->beginTransaction();

      $profileCheck = $this->db->prepare('SELECT id FROM student_profiles WHERE user_id = ? LIMIT 1');
      $profileCheck->execute([$userId]);
      $profileId = $profileCheck->fetchColumn();

      if ($profileId) {
        $updateProfile = $this->db->prepare("UPDATE student_profiles
            SET branch_id = ?, course_id = ?, start_date = ?, preferred_time = ?, preferred_days = ?, vehicle_type = CASE WHEN ? <> '' THEN ? ELSE vehicle_type END, updated_at = NOW()
          WHERE user_id = ?");
        $updateProfile->execute([
          $branchId,
          $courseId,
          $startDateValue,
          $preferredTime,
          $preferredDaysValue,
          $vehicleType,
          $vehicleType,
          $userId,
        ]);
      } else {
        $insertProfile = $this->db->prepare("INSERT INTO student_profiles
            (user_id, branch_id, course_id, start_date, preferred_time, preferred_days, vehicle_type, created_at, updated_at)
          VALUES (?,?,?,?,?,?,?,NOW(),NOW())");
        $insertProfile->execute([
          $userId,
          $branchId,
          $courseId,
          $startDateValue,
          $preferredTime,
          $preferredDaysValue,
          $vehicleType !== '' ? $vehicleType : null,
        ]);
      }

      $updateUser = $this->db->prepare('UPDATE users SET branch_id = ?, updated_at = NOW() WHERE id = ?');
      $updateUser->execute([$branchId, $userId]);

      $issueDate = new DateTimeImmutable('today');
      $dueDate = null;
      if ($startDateValue) {
        $dueDate = new DateTimeImmutable($startDateValue);
      }
      if (!$dueDate || $dueDate < $issueDate) {
        $dueDate = $issueDate->modify('+7 days');
      }

      $invoiceStmt = $this->db->prepare("INSERT INTO invoices (student_id, course_id, amount, issued_date, due_date, status, created_at, updated_at)
        VALUES (?,?,?,?,?,?,NOW(),NOW())");
      $invoiceStmt->execute([
        $userId,
        $course['id'],
        $course['price'],
        $issueDate->format('Y-m-d'),
        $dueDate->format('Y-m-d'),
        'pending',
      ]);

      $this->db->commit();
    } catch (Exception $e) {
      $this->db->rollBack();
      $this->flash('flash_error', 'We could not update your enrolment. Please try again.');
      $this->redirect('index.php?url=student/enrollment');
    }

    $_SESSION['branch_id'] = $branchId;

    $branchStmt = $this->db->prepare('SELECT name FROM branches WHERE id = ? LIMIT 1');
    $branchStmt->execute([$branchId]);
    $branchName = $branchStmt->fetchColumn();
    if ($branchName) {
      $_SESSION['branch_name'] = $branchName;
    }

    $this->flash('flash_success', 'Your enrolment has been updated and a new invoice was issued.');
    $this->redirect('index.php?url=student/dashboard');
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

    if ($firstName === '') {
      $errors[] = 'First name is required.';
    }
    if ($lastName === '') {
      $errors[] = 'Last name is required.';
    }
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

  public function edit($id = null) {
    $this->requireRole('admin');

    $id = $id ?? (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $stmt = $this->db->prepare("SELECT 
        u.id,
        u.branch_id   AS user_branch_id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone       AS user_phone,
        sp.id         AS profile_id,
        sp.vehicle_type,
        sp.course_id,
        sp.branch_id  AS profile_branch_id,
        sp.address,
        sp.phone      AS profile_phone,
        sp.preferred_days,
        sp.preferred_time,
        sp.start_date
      FROM users u
      LEFT JOIN student_profiles sp ON sp.user_id = u.id
      WHERE u.id = ? AND u.role = 'student'
      LIMIT 1");
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $student = [
      'id'         => (int)$record['id'],
      'first_name' => $record['first_name'],
      'last_name'  => $record['last_name'],
      'email'      => $record['email'],
      'phone'      => $record['user_phone'],
      'branch_id'  => $record['user_branch_id'],
    ];

    $profile = null;
    if (!empty($record['profile_id'])) {
      $profile = [
        'id'             => (int)$record['profile_id'],
        'vehicle_type'   => $record['vehicle_type'],
        'course_id'      => $record['course_id'],
        'branch_id'      => $record['profile_branch_id'] ?? $record['user_branch_id'],
        'address'        => $record['address'],
        'phone'          => $record['profile_phone'] ?? $record['user_phone'],
        'preferred_days' => $record['preferred_days'],
        'preferred_time' => $record['preferred_time'],
        'start_date'     => $record['start_date'],
      ];
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

    $branches = $this->db->query("SELECT id, name FROM branches ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $courses  = $this->db->query("SELECT id, name, price, class_count FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $dayOptions = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $timeOptions = ['Morning','Afternoon','Evening'];

    $this->view('student/edit', [
      'student'     => $student,
      'profile'     => $profile,
      'form'        => $form,
      'branches'    => $branches,
      'courses'     => $courses,
      'dayOptions'  => $dayOptions,
      'timeOptions' => $timeOptions,
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
    $branchId  = isset($_POST['branch_id']) ? (int)$_POST['branch_id'] : 0;
    $vehicle   = strtolower(trim($_POST['vehicle_type'] ?? ''));
    $courseId  = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
    $address   = trim($_POST['address'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $preferredTime = trim($_POST['preferred_time'] ?? '');
    $preferredDaysInput = $_POST['preferred_days'] ?? [];

    $errors = [];

    if ($firstName === '') {
      $errors[] = 'First name is required.';
    }
    if ($lastName === '') {
      $errors[] = 'Last name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'A valid email address is required.';
    }

    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
      $errors[] = 'Email address is already in use.';
    }

    if ($branchId <= 0) {
      $errors[] = 'Please select a branch.';
    } else {
      $branchCheck = $this->db->prepare("SELECT id FROM branches WHERE id = ? LIMIT 1");
      $branchCheck->execute([$branchId]);
      if (!$branchCheck->fetch(PDO::FETCH_ASSOC)) {
        $errors[] = 'Selected branch does not exist.';
      }
    }

    $allowedVehicles = ['car','motorcycle'];
    if ($vehicle === '' || !in_array($vehicle, $allowedVehicles, true)) {
      $errors[] = 'Please choose a valid vehicle type.';
    }

    if ($courseId <= 0) {
      $errors[] = 'Please select a course.';
    } else {
      $courseCheck = $this->db->prepare("SELECT id FROM courses WHERE id = ? LIMIT 1");
      $courseCheck->execute([$courseId]);
      if (!$courseCheck->fetch(PDO::FETCH_ASSOC)) {
        $errors[] = 'Selected course does not exist.';
      }
    }

    $allowedDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $preferredDays = [];
    if (is_array($preferredDaysInput)) {
      foreach ($preferredDaysInput as $day) {
        $day = trim((string)$day);
        if (in_array($day, $allowedDays, true) && !in_array($day, $preferredDays, true)) {
          $preferredDays[] = $day;
        }
      }
    }
    $preferredDaysString = implode(',', $preferredDays);

    $allowedTimes = ['', 'Morning', 'Afternoon', 'Evening'];
    if (!in_array($preferredTime, $allowedTimes, true)) {
      $errors[] = 'Please choose a valid preferred time.';
    }

    $startDateValue = null;
    if ($startDate !== '') {
      $dateObj = DateTime::createFromFormat('Y-m-d', $startDate);
      if (!$dateObj) {
        $errors[] = 'Start date must be in YYYY-MM-DD format.';
      } else {
        $startDateValue = $dateObj->format('Y-m-d');
      }
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
       SET branch_id = ?, first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW()
       WHERE id = ? AND role = 'student'"
    );
    $update->execute([
      $branchId,
      $firstName,
      $lastName,
      $email,
      $phone !== '' ? $phone : null,
      $id,
    ]);

    $profileStmt = $this->db->prepare("SELECT id FROM student_profiles WHERE user_id = ? LIMIT 1");
    $profileStmt->execute([$id]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    $profilePhone = $phone !== '' ? $phone : null;
    $preferredTimeValue = $preferredTime !== '' ? $preferredTime : null;
    $addressValue = $address !== '' ? $address : null;

    if ($profile) {
      $profileUpdate = $this->db->prepare(
        "UPDATE student_profiles
         SET vehicle_type = ?, course_id = ?, branch_id = ?, address = ?, phone = ?, preferred_days = ?, preferred_time = ?, start_date = ?, updated_at = NOW()
         WHERE user_id = ?"
      );
      $profileUpdate->execute([
        $vehicle,
        $courseId,
        $branchId,
        $addressValue,
        $profilePhone,
        $preferredDaysString !== '' ? $preferredDaysString : null,
        $preferredTimeValue,
        $startDateValue,
        $id,
      ]);
    } else {
      $profileInsert = $this->db->prepare(
        "INSERT INTO student_profiles
          (user_id, vehicle_type, course_id, branch_id, address, phone, preferred_days, preferred_time, start_date, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
      );
      $profileInsert->execute([
        $id,
        $vehicle,
        $courseId,
        $branchId,
        $addressValue,
        $profilePhone,
        $preferredDaysString !== '' ? $preferredDaysString : null,
        $preferredTimeValue,
        $startDateValue,
      ]);
    }

    if (function_exists('clear_old_input')) {
      clear_old_input();
    }

    $this->flash('flash_success', 'Student details updated.');
    $this->redirect('index.php?url=student/index');
  }

  public function show($id = null) {
    $this->requireRole('admin');

    $id = $id ?? (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $stmt = $this->db->prepare("SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone       AS user_phone,
        u.branch_id   AS user_branch_id,
        bu.name       AS user_branch_name,
        sp.vehicle_type,
        sp.course_id,
        sp.branch_id  AS profile_branch_id,
        sp.address,
        sp.phone      AS profile_phone,
        sp.preferred_days,
        sp.preferred_time,
        sp.start_date,
        bp.name       AS profile_branch_name,
        c.name        AS course_name
      FROM users u
      LEFT JOIN student_profiles sp ON sp.user_id = u.id
      LEFT JOIN branches bu ON bu.id = u.branch_id
      LEFT JOIN branches bp ON bp.id = sp.branch_id
      LEFT JOIN courses c ON c.id = sp.course_id
      WHERE u.id = ? AND u.role = 'student'
      LIMIT 1");
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
      $this->flash('flash_error', 'Student not found.');
      $this->redirect('index.php?url=student/index');
    }

    $student = [
      'id'        => (int)$record['id'],
      'first_name'=> $record['first_name'],
      'last_name' => $record['last_name'],
      'email'     => $record['email'],
      'phone'     => $record['user_phone'],
      'branch'    => $record['profile_branch_name'] ?? $record['user_branch_name'],
    ];

    $profile = null;
    if (!empty($record['course_id']) || !empty($record['vehicle_type']) || !empty($record['profile_branch_id'])) {
      $profile = [
        'vehicle_type'   => $record['vehicle_type'],
        'course_name'    => $record['course_name'],
        'branch_name'    => $record['profile_branch_name'] ?? $record['user_branch_name'],
        'address'        => $record['address'],
        'phone'          => $record['profile_phone'] ?? $record['user_phone'],
        'preferred_days' => $record['preferred_days'],
        'preferred_time' => $record['preferred_time'],
        'start_date'     => $record['start_date'],
      ];
    }

    $this->view('student/show', [
      'student' => $student,
      'profile' => $profile,
    ]);
  }
}