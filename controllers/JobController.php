<?php
class JobController extends Controller {

  public function apply() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name  = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $phone = trim($_POST['phone'] ?? '');
      $license = trim($_POST['license_no'] ?? '');
      $exp   = intval($_POST['experience_years'] ?? 0);

      // handle resume upload
      $resumePath = null;
      if (!empty($_FILES['resume']['name'])) {
        $targetDir = "uploads/resumes/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $resumePath = $targetDir . time() . "_" . basename($_FILES['resume']['name']);
        move_uploaded_file($_FILES['resume']['tmp_name'], $resumePath);
      }

      $stmt = $this->db->prepare("INSERT INTO job_applications 
        (name,email,phone,license_no,experience_years,resume_path) 
        VALUES (?,?,?,?,?,?)");
      $stmt->execute([$name,$email,$phone,$license,$exp,$resumePath]);

      $this->flash('flash_success',"Application submitted. We’ll contact you soon.");
      $this->redirect("index.php?url=job/apply");
    }

    $this->view("job/apply", [
      'flash_error'   => $this->takeFlash('flash_error'),
      'flash_success' => $this->takeFlash('flash_success')
    ]);
  }

  // For admin to review applications
  public function list() {
    $stmt = $this->db->query("SELECT * FROM job_applications ORDER BY created_at DESC");
    $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $this->view("job/list", ['apps'=>$apps]);
  }
}
