<?php
class InstructorController extends Controller {

  public function index() {
    $sql = "SELECT u.id,
                   u.first_name,
                   u.last_name,
                   CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                   u.email,
                   u.phone,
                   i.experience,
                   i.address,
                   i.photo,
                   i.created_at
            FROM users u
            LEFT JOIN instructors i ON u.id = i.user_id
            WHERE u.role='instructor'
            ORDER BY u.created_at DESC";
    $stmt = $this->db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $this->view('instructor/index', ['instructors' => $rows]);
  }

  public function dashboard() {
    $this->requireRole('instructor');
    $this->view("instructor/dashboard");
  }

  public function create() {
    $this->requireRole('admin');
    $this->view("instructor/create");
  }

  public function store() {
    $this->requireRole(['admin']);

    $fn     = trim($_POST['first_name'] ?? '');
    $ln     = trim($_POST['last_name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $addr   = trim($_POST['address'] ?? '');
    $exp    = trim($_POST['experience'] ?? '');

    // insert into users first
    $sqlUser = "INSERT INTO users (first_name, last_name, email, phone, role, created_at)
                VALUES (?,?,?,?, 'instructor', NOW())";
    $this->db->prepare($sqlUser)->execute([$fn, $ln, $email, $phone]);
    $userId = $this->db->lastInsertId();

    // handle photo upload after we have $userId
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
      $targetDir = __DIR__ . "/../public/assets/images/instructors/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);

      $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
      $photo = "instructor_" . $userId . "." . $ext;

      move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photo);
    }

    // insert into instructors (extra info)
    $sqlInst = "INSERT INTO instructors (user_id, name, experience, address, phone, photo, created_at)
                VALUES (?,?,?,?,?,?,NOW())";
    $this->db->prepare($sqlInst)->execute([$userId, "$fn $ln", $exp, $addr, $phone, $photo]);

    $this->flash("flash_success", "Instructor added successfully.");
    $this->redirect("index.php?url=instructor/index");
  }

  public function edit($id) {
    $this->requireRole('admin');
    $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone,
                   i.experience, i.address, i.photo
            FROM users u
            LEFT JOIN instructors i ON u.id = i.user_id
            WHERE u.id=? AND u.role='instructor'";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([(int)$id]);
    $inst = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$inst) {
      $this->flash('flash_error','Not found.');
      $this->redirect("index.php?url=instructor/index");
    }
    $this->view("instructor/edit", ['instructor'=>$inst]);
  }

  public function update($id) {
    $this->requireRole('admin');
    $fn     = trim($_POST['first_name'] ?? '');
    $ln     = trim($_POST['last_name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $addr   = trim($_POST['address'] ?? '');
    $exp    = trim($_POST['experience'] ?? '');

    // update users
    $sqlUser = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, updated_at=NOW()
                WHERE id=? AND role='instructor'";
    $this->db->prepare($sqlUser)->execute([$fn, $ln, $email, $phone, (int)$id]);

    // update instructors
    $sqlInst = "UPDATE instructors SET name=?, experience=?, address=?, phone=?, updated_at=NOW()
                WHERE user_id=?";
    $this->db->prepare($sqlInst)->execute(["$fn $ln", $exp, $addr, $phone, (int)$id]);

    // handle photo replacement
    if (!empty($_FILES['photo']['name'])) {
      $targetDir = __DIR__ . "/../public/assets/images/instructors/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);

      $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
      $photo = "instructor_" . $id . "." . $ext;

      move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photo);

      $sqlPhoto = "UPDATE instructors SET photo=?, updated_at=NOW() WHERE user_id=?";
      $this->db->prepare($sqlPhoto)->execute([$photo, (int)$id]);
    }

    $this->flash('flash_success','Instructor updated.');
    $this->redirect("index.php?url=instructor/index");
  }

  public function destroy($id) {
    $this->requireRole('admin');
    $this->db->prepare("DELETE FROM instructors WHERE user_id=?")->execute([(int)$id]);
    $this->db->prepare("DELETE FROM users WHERE id=? AND role='instructor'")->execute([(int)$id]);
    $this->flash('flash_success','Instructor removed.');
    $this->redirect("index.php?url=instructor/index");
  }
}
