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
      $stmt = $this->db->prepare("SELECT id, email, phone, password, role, first_name, last_name, branch_id
                                  FROM users
                                  WHERE email=? OR phone=?
                                  LIMIT 1");
      $stmt->execute([$input, $input]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($pass, $user['password'])) {
        @session_start();
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['role']       = $user['role'];

        // ✅ Set full name info in session
        $_SESSION['first_name'] = $user['first_name'] ?? '';
        $_SESSION['last_name']  = $user['last_name'] ?? '';
        $_SESSION['student_name'] = trim(($_SESSION['first_name']) . ' ' . ($_SESSION['last_name']));

        // Branch lookup
        $branchId = $user['branch_id'] ?? null;
        $branchName = null;
        if ($branchId) {
          $branchStmt = $this->db->prepare("SELECT name FROM branches WHERE id=? LIMIT 1");
          $branchStmt->execute([(int)$branchId]);
          $branchName = $branchStmt->fetchColumn() ?: null;
        } elseif ($user['role'] === 'student') {
          $profileBranch = $this->db->prepare("SELECT branch_id FROM student_profiles WHERE user_id = ? LIMIT 1");
          $profileBranch->execute([(int)$user['id']]);
          $branchId = $profileBranch->fetchColumn();
          if ($branchId) {
            $branchStmt = $this->db->prepare("SELECT name FROM branches WHERE id=? LIMIT 1");
            $branchStmt->execute([(int)$branchId]);
            $branchName = $branchStmt->fetchColumn() ?: null;
          }
        }
        if ($branchId) {
          $_SESSION['branch_id'] = (int)$branchId;
        }
        if ($branchName) {
          $_SESSION['branch_name'] = $branchName;
        }

        $this->flash('flash_success', "Welcome back, " . $_SESSION['student_name'] . "!");
        $this->redirect("index.php?url=dashboard/index");
      }

      // login failed
      $this->flash('flash_error', "Invalid email/phone or password.");
      return $this->view("auth/login", [
        'flash_error'   => $this->takeFlash('flash_error'),
        'flash_success' => $this->takeFlash('flash_success')
      ]);
    }

    // GET request → show login form
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
      $email  = trim($_POST['email'] ?? '');
      $fn     = trim($_POST['first_name'] ?? '');
      $ln     = trim($_POST['last_name'] ?? '');
      $phone  = trim($_POST['phone'] ?? '');
      $pass   = $_POST['password'] ?? '';
      $role   = 'student';

      $branch_id      = $_POST['branch_id'] ?? null;
      $vehicle_type   = $_POST['vehicle_type'] ?? null;
      $course_id      = $_POST['course_id'] ?? null;
      $address        = trim($_POST['address'] ?? '');
      $preferred_days = $_POST['preferred_days'] ?? null;
      $preferred_time = $_POST['preferred_time'] ?? null;
      $start_date     = $_POST['start_date'] ?? null;

      // Convert arrays to strings
      if (is_array($course_id)) {
        $course_id = implode(',', $course_id);
      }
      if (is_array($preferred_days)) {
        $preferred_days = implode(',', $preferred_days);
      }
      if (is_array($preferred_time)) {
        $preferred_time = implode(',', $preferred_time);
      }

      // Duplicate email check
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

      // Generate invoice for chosen course
      $courseStmt = $this->db->prepare("SELECT id, price FROM courses WHERE id=? LIMIT 1");
      $courseStmt->execute([$course_id]);
      $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

      if ($course) {
        $issueDate = new DateTimeImmutable('today');
        $dueDate = null;

        if (!empty($start_date)) {
          $startDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $start_date);
          if ($startDateObj instanceof DateTimeImmutable) {
            $dueDate = $startDateObj;
          }
        }

        if (!$dueDate || $dueDate < $issueDate) {
          $dueDate = $issueDate->modify('+7 days');
        }

        $invoiceStmt = $this->db->prepare("INSERT INTO invoices (student_id, course_id, amount, issued_date, due_date, status, created_at, updated_at)
          VALUES (?,?,?,?,?,?,NOW(),NOW())");
        $invoiceStmt->execute([
          $user_id,
          $course['id'],
          $course['price'],
          $issueDate->format('Y-m-d'),
          $dueDate->format('Y-m-d'),
          'pending',
        ]);
      }

      $this->flash('flash_success', "Account created. Please log in.");
      $this->redirect("index.php?url=auth/login");
    }

    // GET request → show form
    $this->view("auth/register", $this->registrationData());
  }

  /** helper to fetch branches & courses for register view */
  private function registrationData(): array {
    $branches = $this->db->query("SELECT id, name FROM branches ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $courses  = $this->db->query("SELECT id, name, price, class_count FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    return [
      'branches'      => $branches,
      'courses'       => $courses,
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
    $this->flash('flash_success', "You have been logged out.");
    $this->redirect("index.php?url=auth/login");
  }
}
