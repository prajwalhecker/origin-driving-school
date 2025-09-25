<?php
class ScheduleController extends Controller {

  public function create(){ 
    $this->requireRole(['admin','instructor']); 
    $this->view("schedule/create"); 
  }

  public function store() {
    $student_id    = $_POST['student_id'];
    $instructor_id = $_POST['instructor_id'];
    $course_id     = $_POST['course_id'];
    $branch_id     = $_POST['branch_id'];
    $vehicle_id    = $_POST['vehicle_id'];
    $start_time    = $_POST['start_time'];
    $end_time      = $_POST['end_time'];

    // Conflict check → same student or instructor at overlapping times
    $stmt = $this->db->prepare("
      SELECT * FROM bookings 
      WHERE 
        (
          (instructor_id = :instructor_id) 
          OR (student_id = :student_id)
        )
        AND (
          (start_time < :end_time AND end_time > :start_time)
        )
    ");
    $stmt->execute([
      ':instructor_id' => $instructor_id,
      ':student_id'    => $student_id,
      ':start_time'    => $start_time,
      ':end_time'      => $end_time
    ]);

    $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($conflict) {
      $this->flash("flash_error", "Conflict detected: Instructor or student already has a class at this time.");
      return $this->redirect("index.php?url=schedule/create");
    }

    // Insert booking
    $stmt = $this->db->prepare("
      INSERT INTO bookings (student_id, instructor_id, course_id, branch_id, vehicle_id, start_time, end_time, status, created_at) 
      VALUES (?,?,?,?,?,?,?, 'booked', NOW())
    ");
    $stmt->execute([$student_id, $instructor_id, $course_id, $branch_id, $vehicle_id, $start_time, $end_time]);

    $this->flash("flash_success", "Booking created successfully.");
    $this->redirect("index.php?url=schedule/index");
  }

  public function index(){
    $this->requireRole(['admin','instructor','student']);
    $role = $_SESSION['role'];

    if ($role === 'admin') {
      $stmt = $this->db->query("SELECT * FROM bookings ORDER BY start_time ASC");
    } elseif ($role === 'instructor') {
      $stmt = $this->db->prepare("SELECT * FROM bookings WHERE instructor_id=? ORDER BY start_time ASC");
      $stmt->execute([$_SESSION['user_id']]);
    } else {
      $stmt = $this->db->prepare("SELECT * FROM bookings WHERE student_id=? ORDER BY start_time ASC");
      $stmt->execute([$_SESSION['user_id']]);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $this->view("schedule/index", ['bookings' => $rows]);
  }

  public function edit($id){
    $this->requireRole(['admin','instructor']);
    $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id=?"); 
    $stmt->execute([(int)$id]);
    $b = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$b){ 
      $this->flash('flash_error','Booking not found.'); 
      $this->redirect("index.php?url=schedule/index"); 
    }
    $this->view("schedule/edit", ['booking'=>$b]);
  }

  public function update($id){
    $this->requireRole(['admin','instructor']);
    $status = $_POST['status'] ?? 'booked';
    $stmt = $this->db->prepare("UPDATE bookings SET status=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$status, (int)$id]);
    $this->flash('flash_success','Booking updated.'); 
    $this->redirect("index.php?url=schedule/index");
  }

  public function destroy($id){
    $this->requireRole(['admin','instructor']);
    $this->db->prepare("DELETE FROM bookings WHERE id=?")->execute([(int)$id]);
    $this->flash('flash_success','Booking deleted.'); 
    $this->redirect("index.php?url=schedule/index");
  }
}
