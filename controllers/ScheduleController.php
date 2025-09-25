<?php
class ScheduleController extends Controller {

  public function create(){
    $this->requireRole(['admin','instructor']);

    $students = $this->db->query("SELECT id, first_name, last_name FROM users WHERE role='student' ORDER BY first_name, last_name")
      ->fetchAll(PDO::FETCH_ASSOC);

    $instructors = $this->db->query("SELECT id, first_name, last_name FROM users WHERE role='instructor' ORDER BY first_name, last_name")
      ->fetchAll(PDO::FETCH_ASSOC);

    $courses = $this->db->query("SELECT id, name FROM courses ORDER BY name")
      ->fetchAll(PDO::FETCH_ASSOC);

    $branches = $this->db->query("SELECT id, name FROM branches ORDER BY name")
      ->fetchAll(PDO::FETCH_ASSOC);

    $vehicles = $this->db->query("SELECT id, registration_number, make, model FROM vehicles ORDER BY registration_number")
      ->fetchAll(PDO::FETCH_ASSOC);

    $this->view("schedule/create", [
      'students'    => $students,
      'instructors' => $instructors,
      'courses'     => $courses,
      'branches'    => $branches,
      'vehicles'    => $vehicles
    ]);
  }

  public function store() {
    $student_id    = trim($_POST['student_id'] ?? '');
    $instructor_id = trim($_POST['instructor_id'] ?? '');
    $course_id     = $_POST['course_id'] ?? null;
    $branch_id     = $_POST['branch_id'] ?? null;
    $vehicle_id    = $_POST['vehicle_id'] ?? null;

    $rawStart      = $_POST['start_time'] ?? $_POST['start_datetime'] ?? '';
    $rawEnd        = $_POST['end_time'] ?? $_POST['end_datetime'] ?? '';

    $start_time    = $rawStart ? str_replace('T', ' ', $rawStart) : '';
    $end_time      = $rawEnd ? str_replace('T', ' ', $rawEnd) : '';

    if (!$student_id || !$instructor_id || !$start_time || !$end_time) {
      $this->flash("flash_error", "All required fields must be completed.");
      return $this->redirect("index.php?url=schedule/create");
    }

    if (strtotime($end_time) <= strtotime($start_time)) {
      $this->flash("flash_error", "The lesson end time must be after the start time.");
      return $this->redirect("index.php?url=schedule/create");
    }

    $course_id  = $course_id === '' ? null : $course_id;
    $branch_id  = $branch_id === '' ? null : $branch_id;
    $vehicle_id = $vehicle_id === '' ? null : $vehicle_id;

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
    $role   = $_SESSION['role'];
    $userId = $_SESSION['user_id'];

    $statusFilter = $_GET['status'] ?? 'all';
    $windowFilter = $_GET['window'] ?? 'upcoming';

    $baseConditions = [];
    $baseParams = [];

    if ($role === 'instructor') {
      $baseConditions[] = 'b.instructor_id = :user_id';
      $baseParams[':user_id'] = $userId;
    } elseif ($role === 'student') {
      $baseConditions[] = 'b.student_id = :user_id';
      $baseParams[':user_id'] = $userId;
    }

    $conditions = $baseConditions;
    $params = $baseParams;

    if ($statusFilter !== 'all') {
      $conditions[] = 'b.status = :status';
      $params[':status'] = $statusFilter;
    }

    $now = date('Y-m-d H:i:s');
    if ($windowFilter === 'upcoming') {
      $conditions[] = 'b.start_time >= :now';
      $params[':now'] = $now;
    } elseif ($windowFilter === 'past') {
      $conditions[] = 'b.end_time < :now';
      $params[':now'] = $now;
    }

    $sql = "SELECT b.*, 
              CONCAT(stu.first_name, ' ', stu.last_name) AS student_name,
              CONCAT(inst.first_name, ' ', inst.last_name) AS instructor_name,
              c.name AS course_name,
              br.name AS branch_name,
              v.registration_number AS vehicle_reg
            FROM bookings b
            LEFT JOIN users stu ON b.student_id = stu.id
            LEFT JOIN users inst ON b.instructor_id = inst.id
            LEFT JOIN courses c ON b.course_id = c.id
            LEFT JOIN branches br ON b.branch_id = br.id
            LEFT JOIN vehicles v ON b.vehicle_id = v.id";

    if ($conditions) {
      $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY b.start_time ASC';

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary metrics scoped to the viewer
    $scopeWhere = $baseConditions ? (' WHERE ' . implode(' AND ', $baseConditions)) : '';

    $summaryCounts = ['booked' => 0, 'completed' => 0, 'cancelled' => 0];
    $summarySql = "SELECT status, COUNT(*) AS total FROM bookings b" . $scopeWhere . " GROUP BY status";
    $summaryStmt = $this->db->prepare($summarySql);
    $summaryStmt->execute($baseParams);
    foreach ($summaryStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $summaryCounts[$row['status']] = (int)$row['total'];
    }

    $upcomingSql = "SELECT COUNT(*) FROM bookings b" .
      ($scopeWhere ? $scopeWhere . " AND b.start_time >= :now" : " WHERE b.start_time >= :now");
    $upcomingStmt = $this->db->prepare($upcomingSql);
    $upcomingParams = $baseParams;
    $upcomingParams[':now'] = $now;
    $upcomingStmt->execute($upcomingParams);
    $upcomingCount = (int)$upcomingStmt->fetchColumn();

    $nextSql = "SELECT b.*, 
                  CONCAT(stu.first_name, ' ', stu.last_name) AS student_name,
                  CONCAT(inst.first_name, ' ', inst.last_name) AS instructor_name,
                  c.name AS course_name,
                  br.name AS branch_name,
                  v.registration_number AS vehicle_reg
                FROM bookings b
                LEFT JOIN users stu ON b.student_id = stu.id
                LEFT JOIN users inst ON b.instructor_id = inst.id
                LEFT JOIN courses c ON b.course_id = c.id
                LEFT JOIN branches br ON b.branch_id = br.id
                LEFT JOIN vehicles v ON b.vehicle_id = v.id" .
      ($scopeWhere ? $scopeWhere . " AND b.start_time >= :now" : " WHERE b.start_time >= :now") .
      " ORDER BY b.start_time ASC LIMIT 1";
    $nextStmt = $this->db->prepare($nextSql);
    $nextStmt->execute($upcomingParams);
    $nextBooking = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    $this->view("schedule/index", [
      'bookings'      => $rows,
      'filters'       => ['status' => $statusFilter, 'window' => $windowFilter],
      'summary'       => [
        'total'     => array_sum($summaryCounts),
        'booked'    => $summaryCounts['booked'],
        'completed' => $summaryCounts['completed'],
        'cancelled' => $summaryCounts['cancelled'],
        'upcoming'  => $upcomingCount
      ],
      'nextBooking'   => $nextBooking
    ]);
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
    $stmt = $this->db->prepare("UPDATE bookings SET status=? WHERE id=?");
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
