<?php
class AuthController extends Controller {

  /**
   * Login (email or phone)
   */
  public function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $input = trim($_POST['email_or_phone'] ?? '');
      $pass  = $_POST['password'] ?? '';

      // allow login by email OR phone
      $stmt = $this->db->prepare("SELECT id, email, phone, password, role 
                                  FROM users 
                                  WHERE email=? OR phone=? 
                                  LIMIT 1");
      $stmt->execute([$input, $input]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      
      if ($user && password_verify($pass, $user['password'])) {
    @session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];
    $this->flash('flash_success', "Welcome back!");

    if ($user['role'] === 'admin')       $this->redirect("index.php?url=dashboard/admin");
    if ($user['role'] === 'instructor')  $this->redirect("index.php?url=instructor/dashboard");
    $this->redirect("index.php?url=student/dashboard");
}


      $this->flash('flash_error', "Invalid email/phone or password.");
      return $this->view("auth/login");
    }

    // GET request → just show login view
    $this->view("auth/login", [
      'flash_error'   => $this->takeFlash('flash_error'),
      'flash_success' => $this->takeFlash('flash_success')
    ]);
  }

  /**
   * Register
   */
public function register() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $fn    = trim($_POST['first_name'] ?? '');
    $ln    = trim($_POST['last_name'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $branch_id = (int)($_POST['branch_id'] ?? 0);
    $course_id = (int)($_POST['course_id'] ?? 0);
    $vehicle_type = $_POST['vehicle_type'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $preferred_time = $_POST['preferred_time'] ?? '';
    $preferred_days = isset($_POST['preferred_days']) ? implode(',', (array)$_POST['preferred_days']) : '';

    // Force student role
    $role = 'student';

    // Basic validations
    if (!$email || !$fn || !$ln || strlen($pass) < 6 || $pass !== $pass2 || !$branch_id || !$course_id || !$vehicle_type) {
      $this->flash('flash_error', "Please fill all required fields and ensure passwords match.");
      return $this->view("auth/register", $this->registrationData());
    }

    // Duplicate email?
    $stmt = $this->db->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $this->flash('flash_error', "Email already registered.");
      return $this->view("auth/register", $this->registrationData());
    }

    // Create user
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $this->db->prepare("INSERT INTO users
      (branch_id, created_at, email, first_name, last_name, password, phone, role, updated_at)
      VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$branch_id, $email, $fn, $ln, $hash, $phone, $role]);

    $user_id = (int)$this->db->lastInsertId();

    // Create student profile
    $stmt = $this->db->prepare("INSERT INTO student_profiles
      (user_id, vehicle_type, course_id, branch_id, address, phone, preferred_days, preferred_time, start_date)
      VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$user_id, $vehicle_type, $course_id, $branch_id, $address, $phone, $preferred_days, $preferred_time, $start_date]);

    $this->flash('flash_success', "Account created. Please log in.");
    $this->redirect("index.php?url=auth/login");
  }

  // GET → show form with branches & courses
  $this->view("auth/register", $this->registrationData());
}

/** helper to fetch branches & courses for register view */
private function registrationData(): array {
  $branches = $this->db->query("SELECT id, name FROM branches ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
  $courses  = $this->db->query("SELECT id, name, price, class_count FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
  return [
    'branches' => $branches,
    'courses'  => $courses,
    'flash_error'   => $this->takeFlash('flash_error'),
    'flash_success' => $this->takeFlash('flash_success')
  ];
}



  /**
   * Logout
   */
  public function logout() {
    @session_start(); 
    session_destroy();
    $this->flash('flash_success', "Logged out.");
    $this->redirect("index.php?url=auth/login");
  }

  /**
   * Forgot Password
   */
  public function forgot() {
    $this->view("auth/forgot"); 
  }
}
