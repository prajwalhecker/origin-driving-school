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

    $rawStart = $_POST['start_time'] ?? '';
    $rawEnd   = $_POST['end_time'] ?? '';

    // ✅ Convert to full MySQL datetime format (YYYY-MM-DD HH:MM:SS)
    $start_time = $rawStart ? date('Y-m-d H:i:s', strtotime($rawStart)) : '';
    $end_time   = $rawEnd   ? date('Y-m-d H:i:s', strtotime($rawEnd)) : '';

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

    // Conflict check
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
      INSERT INTO bookings (student_id, instructor_id, course_id, branch_id, vehicle_id, start_time, end_time, status, created_at, updated_at)
      VALUES (?,?,?,?,?,?,?, 'booked', NOW(), NOW())
    ");
    $ok = $stmt->execute([$student_id, $instructor_id, $course_id, $branch_id, $vehicle_id, $start_time, $end_time]);

    if (!$ok) {
      $error = $stmt->errorInfo();
      die("Booking insert failed: " . implode(" | ", $error));
    }

    $this->flash("flash_success", "Booking created successfully.");
    $this->redirect("index.php?url=schedule/index");
}


  public function index(){
    $this->requireRole(['admin','instructor','student']);
    $role   = $_SESSION['role'];
    $userId = $_SESSION['user_id'];

    $statusFilter = $_GET['status'] ?? 'all';
    $windowFilter = $_GET['window'] ?? 'upcoming';
    $now = date('Y-m-d H:i:s');

    $dataset = null;

    if ($this->tableHasRows('bookings')) {
      $dataset = $this->loadBookingsDataset($role, $userId, $statusFilter, $windowFilter, $now);
    }

    if ($dataset === null && $this->tableHasRows('schedules')) {
      $dataset = $this->loadSchedulesDataset($role, $userId, $statusFilter, $windowFilter, $now);
    }

    if ($dataset === null) {
      $dataset = [
        'rows'           => [],
        'summaryCounts'  => $this->baseStatusBuckets(),
        'upcomingCount'  => 0,
        'thisWeekCount'  => 0,
        'nextBooking'    => null,
      ];
    }

    $summaryCounts = $dataset['summaryCounts'];

    $this->view("schedule/index", [
      'bookings'      => $dataset['rows'],
      'filters'       => ['status' => $statusFilter, 'window' => $windowFilter],
      'summary'       => [
        'total'     => array_sum($summaryCounts),
        'booked'    => $summaryCounts['booked'],
        'scheduled' => $summaryCounts['scheduled'],
        'completed' => $summaryCounts['completed'],
        'cancelled' => $summaryCounts['cancelled'],
        'upcoming'  => $dataset['upcomingCount'],
        'this_week' => $dataset['thisWeekCount']
      ],
      'nextBooking'   => $dataset['nextBooking']
    ]);
  }

  private function loadBookingsDataset(string $role, int $userId, string $statusFilter, string $windowFilter, string $now): array {
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

    if ($windowFilter === 'upcoming') {
      $conditions[] = 'b.start_time >= :now';
      $params[':now'] = $now;
    } elseif ($windowFilter === 'past') {
      $conditions[] = 'b.end_time < :now';
      $params[':now'] = $now;
    }

    $sql = "SELECT b.*,"
             . " CONCAT(stu.first_name, ' ', stu.last_name) AS student_name,"
             . " CONCAT(inst.first_name, ' ', inst.last_name) AS instructor_name,"
             . " c.name AS course_name,"
             . " br.name AS branch_name,"
             . " v.registration_number AS vehicle_reg"
           . " FROM bookings b"
           . " LEFT JOIN users stu ON b.student_id = stu.id"
           . " LEFT JOIN users inst ON b.instructor_id = inst.id"
           . " LEFT JOIN courses c ON b.course_id = c.id"
           . " LEFT JOIN branches br ON b.branch_id = br.id"
           . " LEFT JOIN vehicles v ON b.vehicle_id = v.id";

    if ($conditions) {
      $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY b.start_time ASC';

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $scopeWhere = $baseConditions ? (' WHERE ' . implode(' AND ', $baseConditions)) : '';

    $summaryCounts = $this->baseStatusBuckets();
    $summarySql = "SELECT status, COUNT(*) AS total FROM bookings b" . $scopeWhere . " GROUP BY status";
    $summaryStmt = $this->db->prepare($summarySql);
    $summaryStmt->execute($baseParams);
    foreach ($summaryStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $statusKey = strtolower($row['status']);
      if (!array_key_exists($statusKey, $summaryCounts)) {
        $summaryCounts[$statusKey] = 0;
      }
      $summaryCounts[$statusKey] = (int)$row['total'];
    }

    $upcomingSql = "SELECT COUNT(*) FROM bookings b"
      . ($scopeWhere ? $scopeWhere . " AND b.start_time >= :now" : " WHERE b.start_time >= :now");
    $upcomingStmt = $this->db->prepare($upcomingSql);
    $upcomingParams = $baseParams;
    $upcomingParams[':now'] = $now;
    $upcomingStmt->execute($upcomingParams);
    $upcomingCount = (int)$upcomingStmt->fetchColumn();

    $weekSql = "SELECT COUNT(*) FROM bookings b"
      . ($scopeWhere ? $scopeWhere . " AND b.start_time BETWEEN :now AND :week_end" : " WHERE b.start_time BETWEEN :now AND :week_end");
    $weekStmt = $this->db->prepare($weekSql);
    $weekParams = $baseParams;
    $weekParams[':now'] = $now;
    $weekParams[':week_end'] = date('Y-m-d H:i:s', strtotime('+7 days', strtotime($now)));
    $weekStmt->execute($weekParams);
    $thisWeekCount = (int)$weekStmt->fetchColumn();

    $nextSql = "SELECT b.*,"
               . " CONCAT(stu.first_name, ' ', stu.last_name) AS student_name,"
               . " CONCAT(inst.first_name, ' ', inst.last_name) AS instructor_name,"
               . " c.name AS course_name,"
               . " br.name AS branch_name,"
               . " v.registration_number AS vehicle_reg"
             . " FROM bookings b"
             . " LEFT JOIN users stu ON b.student_id = stu.id"
             . " LEFT JOIN users inst ON b.instructor_id = inst.id"
             . " LEFT JOIN courses c ON b.course_id = c.id"
             . " LEFT JOIN branches br ON b.branch_id = br.id"
             . " LEFT JOIN vehicles v ON b.vehicle_id = v.id"
      . ($scopeWhere ? $scopeWhere . " AND b.start_time >= :now" : " WHERE b.start_time >= :now")
      . " ORDER BY b.start_time ASC LIMIT 1";
    $nextStmt = $this->db->prepare($nextSql);
    $nextStmt->execute($upcomingParams);
    $nextBooking = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    return [
      'rows'           => $rows,
      'summaryCounts'  => $summaryCounts,
      'upcomingCount'  => $upcomingCount,
      'thisWeekCount'  => $thisWeekCount,
      'nextBooking'    => $nextBooking,
    ];
  }

  private function loadSchedulesDataset(string $role, int $userId, string $statusFilter, string $windowFilter, string $now): array {
    $baseConditions = [];
    $baseParams = [];

    if ($role === 'instructor') {
      $baseConditions[] = 's.instructor_id = :user_id';
      $baseParams[':user_id'] = $userId;
    } elseif ($role === 'student') {
      $baseConditions[] = 's.student_id = :user_id';
      $baseParams[':user_id'] = $userId;
    }

    $conditions = $baseConditions;
    $params = $baseParams;

    if ($statusFilter !== 'all') {
      $conditions[] = 's.status = :status';
      $params[':status'] = $statusFilter;
    }

    if ($windowFilter === 'upcoming') {
      $conditions[] = 's.start_datetime >= :now';
      $params[':now'] = $now;
    } elseif ($windowFilter === 'past') {
      $conditions[] = 's.end_datetime < :now';
      $params[':now'] = $now;
    }

    $sql = "SELECT s.*,"
           . " CONCAT(stu.first_name, ' ', stu.last_name) AS student_name,"
           . " CONCAT(inst.first_name, ' ', inst.last_name) AS instructor_name,"
           . " c.name AS course_name,"
           . " br.name AS branch_name"
         . " FROM schedules s"
         . " LEFT JOIN users stu ON s.student_id = stu.id"
         . " LEFT JOIN users inst ON s.instructor_id = inst.id"
         . " LEFT JOIN student_profiles sp ON sp.user_id = s.student_id"
         . " LEFT JOIN courses c ON sp.course_id = c.id"
         . " LEFT JOIN branches br ON sp.branch_id = br.id";

    if ($conditions) {
      $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY s.start_datetime ASC';

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
      $row['start_time'] = $row['start_datetime'];
      $row['end_time'] = $row['end_datetime'];
      $row['vehicle_reg'] = $row['vehicle_reg'] ?? null;
    }
    unset($row);

    $scopeWhere = $baseConditions ? (' WHERE ' . implode(' AND ', $baseConditions)) : '';

    $summaryCounts = $this->baseStatusBuckets();
    $summarySql = "SELECT status, COUNT(*) AS total FROM schedules s" . $scopeWhere . " GROUP BY status";
    $summaryStmt = $this->db->prepare($summarySql);
    $summaryStmt->execute($baseParams);
    foreach ($summaryStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $statusKey = strtolower($row['status']);
      if (!array_key_exists($statusKey, $summaryCounts)) {
        $summaryCounts[$statusKey] = 0;
      }
      $summaryCounts[$statusKey] = (int)$row['total'];
    }

    $upcomingSql = "SELECT COUNT(*) FROM schedules s"
      . ($scopeWhere ? $scopeWhere . " AND s.start_datetime >= :now" : " WHERE s.start_datetime >= :now");
    $upcomingStmt = $this->db->prepare($upcomingSql);
    $upcomingParams = $baseParams;
    $upcomingParams[':now'] = $now;
    $upcomingStmt->execute($upcomingParams);
    $upcomingCount = (int)$upcomingStmt->fetchColumn();

    $weekSql = "SELECT COUNT(*) FROM schedules s"
      . ($scopeWhere ? $scopeWhere . " AND s.start_datetime BETWEEN :now AND :week_end" : " WHERE s.start_datetime BETWEEN :now AND :week_end");
    $weekStmt = $this->db->prepare($weekSql);
    $weekParams = $baseParams;
    $weekParams[':now'] = $now;
    $weekParams[':week_end'] = date('Y-m-d H:i:s', strtotime('+7 days', strtotime($now)));
    $weekStmt->execute($weekParams);
    $thisWeekCount = (int)$weekStmt->fetchColumn();

    $nextSql = "SELECT s.*,"
             . " CONCAT(stu.first_name, ' ', stu.last_name) AS student_name,"
             . " CONCAT(inst.first_name, ' ', inst.last_name) AS instructor_name,"
             . " c.name AS course_name,"
             . " br.name AS branch_name"
           . " FROM schedules s"
           . " LEFT JOIN users stu ON s.student_id = stu.id"
           . " LEFT JOIN users inst ON s.instructor_id = inst.id"
           . " LEFT JOIN student_profiles sp ON sp.user_id = s.student_id"
           . " LEFT JOIN courses c ON sp.course_id = c.id"
           . " LEFT JOIN branches br ON sp.branch_id = br.id"
      . ($scopeWhere ? $scopeWhere . " AND s.start_datetime >= :now" : " WHERE s.start_datetime >= :now")
      . " ORDER BY s.start_datetime ASC LIMIT 1";
    $nextStmt = $this->db->prepare($nextSql);
    $nextStmt->execute($upcomingParams);
    $nextBooking = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($nextBooking) {
      $nextBooking['start_time'] = $nextBooking['start_datetime'];
      $nextBooking['end_time'] = $nextBooking['end_datetime'];
    }

    return [
      'rows'           => $rows,
      'summaryCounts'  => $summaryCounts,
      'upcomingCount'  => $upcomingCount,
      'thisWeekCount'  => $thisWeekCount,
      'nextBooking'    => $nextBooking,
    ];
  }

  private function baseStatusBuckets(): array {
    return [
      'booked'    => 0,
      'scheduled' => 0,
      'completed' => 0,
      'cancelled' => 0,
    ];
  }

  private function tableHasRows(string $table): bool {
    try {
      $stmt = $this->db->query("SELECT COUNT(*) FROM " . $table);
      if (!$stmt) {
        return false;
      }
      return (int)$stmt->fetchColumn() > 0;
    } catch (Exception $e) {
      return false;
    }
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
