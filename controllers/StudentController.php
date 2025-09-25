<?php
class StudentController extends Controller {

  public function index(){ // admin only: list students
    $this->requireRole('admin');
    $q = $this->db->query("SELECT id, first_name, last_name, email, phone FROM users WHERE role='student' ORDER BY created_at DESC");
    $students = $q->fetchAll(PDO::FETCH_ASSOC);
    $this->view("student/index", compact('students'));
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
        $this->flash("flash_error", "Profile not found.");
        $this->redirect("index.php");
    }

    $this->view("student/profile", ['student' => $student]);
}


  public function show($id){
    $this->requireRole(['admin','instructor','student']);
    @session_start();
    if (($_SESSION['role']==='student') && ($_SESSION['user_id'] != (int)$id)) {
      $this->flash('flash_error', 'You can only view your own profile.'); $this->redirect("index.php?url=student/dashboard");
    }
    $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, phone FROM users WHERE id=? AND role='student'");
    $stmt->execute([(int)$id]); $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student){ $this->flash('flash_error','Student not found.'); $this->redirect("index.php?url=student/index"); }
    $this->view("student/show", compact('student'));
  }

  public function create(){ $this->requireRole('admin'); $this->view("student/create"); }

  public function store(){
    $this->requireRole('admin');
    $email = trim($_POST['email']??''); $fn=trim($_POST['first_name']??''); $ln=trim($_POST['last_name']??''); $phone=trim($_POST['phone']??'');
    if (!$email||!$fn||!$ln){ $this->flash('flash_error','All fields required.'); return $this->view("student/create"); }
    $exists = $this->db->prepare("SELECT id FROM users WHERE email=?"); $exists->execute([$email]);
    if ($exists->fetch()){ $this->flash('flash_error','Email already used.'); return $this->view("student/create"); }
    $pw = password_hash($_POST['password'] ?? 'TestPass123', PASSWORD_DEFAULT);
    $stmt = $this->db->prepare("INSERT INTO users (branch_id, created_at, email, first_name, last_name, password, phone, role, updated_at)
                                VALUES (NULL,NOW(),?,?,?,?,?,'student',NOW())");
    $stmt->execute([$email,$fn,$ln,$pw,$phone]);
    $this->flash('flash_success','Student created.'); $this->redirect("index.php?url=student/index");
  }

  public function edit($id){
    $this->requireRole('admin');
    $stmt = $this->db->prepare("SELECT id, email, first_name, last_name, phone FROM users WHERE id=? AND role='student'");
    $stmt->execute([(int)$id]); $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student){ $this->flash('flash_error','Not found.'); $this->redirect("index.php?url=student/index"); }
    $this->view("student/edit", compact('student'));
  }

  public function update($id){
    $this->requireRole('admin');
    $email=trim($_POST['email']??''); $fn=trim($_POST['first_name']??''); $ln=trim($_POST['last_name']??''); $phone=trim($_POST['phone']??'');
    if (!$email||!$fn||!$ln){ $this->flash('flash_error','All fields required.'); return $this->edit($id); }
    $sql = "UPDATE users SET email=?, first_name=?, last_name=?, phone=?, updated_at=NOW() WHERE id=? AND role='student'";
    $this->db->prepare($sql)->execute([$email,$fn,$ln,$phone,(int)$id]);
    $this->flash('flash_success','Student updated.'); $this->redirect("index.php?url=student/index");
  }

  public function destroy($id){
    $this->requireRole('admin');
    $stmt = $this->db->prepare("DELETE FROM users WHERE id=? AND role='student'"); $stmt->execute([(int)$id]);
    $this->flash('flash_success','Student removed.'); $this->redirect("index.php?url=student/index");
  }
}
