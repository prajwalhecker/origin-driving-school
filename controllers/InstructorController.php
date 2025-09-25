<?php
class InstructorController extends Controller {

  public function index(){
    $stmt = $this->db->query("SELECT * FROM instructors ORDER BY created_at DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $this->view("instructor/index", ['instructors' => $rows]);
}


  public function dashboard(){ $this->requireRole('instructor'); $this->view("instructor/dashboard"); }

  public function create(){ $this->requireRole('admin'); $this->view("instructor/create"); }

  public function store(){
    $this->requireRole(['admin']);

    $name       = $_POST['name'];
    $experience = $_POST['experience'];
    $address    = $_POST['address'];
    $phone      = $_POST['phone'];

    // Handle photo upload
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "uploads/instructors/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $photo = time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photo);
    }

    $stmt = $this->db->prepare("INSERT INTO instructors (name, experience, address, phone, photo, created_at) VALUES (?,?,?,?,?,NOW())");
    $stmt->execute([$name, $experience, $address, $phone, $photo]);

    $this->flash("flash_success","Instructor added successfully.");
    $this->redirect("index.php?url=instructor/index");
}


  public function edit($id){
    $this->requireRole('admin');
    $stmt = $this->db->prepare("SELECT id, email, first_name, last_name, phone FROM users WHERE id=? AND role='instructor'");
    $stmt->execute([(int)$id]); $inst = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$inst){ $this->flash('flash_error','Not found.'); $this->redirect("index.php?url=instructor/index"); }
    $this->view("instructor/edit", ['instructor'=>$inst]);
  }

  public function update($id){
    $this->requireRole('admin');
    $email=trim($_POST['email']??''); $fn=trim($_POST['first_name']??''); $ln=trim($_POST['last_name']??''); $phone=trim($_POST['phone']??'');
    $sql="UPDATE users SET email=?, first_name=?, last_name=?, phone=?, updated_at=NOW() WHERE id=? AND role='instructor'";
    $this->db->prepare($sql)->execute([$email,$fn,$ln,$phone,(int)$id]);
    $this->flash('flash_success','Instructor updated.'); $this->redirect("index.php?url=instructor/index");
  }

  public function destroy($id){
    $this->requireRole('admin');
    $this->db->prepare("DELETE FROM users WHERE id=? AND role='instructor'")->execute([(int)$id]);
    $this->flash('flash_success','Instructor removed.'); $this->redirect("index.php?url=instructor/index");
  }
}
