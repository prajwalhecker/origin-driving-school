<?php

class InvoiceController extends Controller {

  public function index(){
    $this->requireRole(['admin','student']);
    @session_start();

    $isStudent = ($_SESSION['role'] ?? '') === 'student';

    $baseSql = <<<SQL
SELECT i.*,
       CONCAT_WS(' ', u.first_name, u.last_name) AS student_name,
       u.email AS student_email,
       c.name AS course_name
FROM invoices i
LEFT JOIN users u ON u.id = i.student_id
LEFT JOIN courses c ON c.id = i.course_id
SQL;

    if ($isStudent) {
      $stmt = $this->db->prepare($baseSql . " WHERE i.student_id = ? ORDER BY i.created_at DESC");
      $stmt->execute([$_SESSION['user_id']]);
    } else {
      $stmt = $this->db->query($baseSql . " ORDER BY i.created_at DESC");
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $metrics = [
      'total'       => count($rows),
      'paid'        => 0,
      'outstanding' => 0.0,
      'overdue'     => 0,
    ];

    $today = new DateTimeImmutable('today');
    foreach ($rows as &$row) {
      $row['amount']  = (float)($row['amount'] ?? 0);
      $row['balance'] = ($row['status'] === 'paid') ? 0 : $row['amount'];
      $row['is_overdue'] = false;

      if (!empty($row['due_date'])) {
        try {
          $dueDate = new DateTimeImmutable($row['due_date']);
          $row['is_overdue'] = ($row['status'] !== 'paid') && ($dueDate < $today);
        } catch (Exception $e) {
          $row['is_overdue'] = false;
        }
      }

      if ($row['status'] === 'paid') {
        $metrics['paid']++;
      } else {
        $metrics['outstanding'] += $row['amount'];
      }

      if ($row['is_overdue']) {
        $metrics['overdue']++;
      }
    }
    unset($row);

    $metrics['outstanding'] = round($metrics['outstanding'], 2);

    $this->view('invoice/index', [
      'invoices'  => $rows,
      'metrics'   => $metrics,
      'isStudent' => $isStudent,
    ]);
  }

  public function create(){
    $this->requireRole('admin');
    $students = $this->db->query("SELECT id, first_name, last_name, email FROM users WHERE role='student' ORDER BY first_name, last_name")
                        ->fetchAll(PDO::FETCH_ASSOC);
    $courses  = $this->db->query("SELECT id, name, price FROM courses ORDER BY name")
                        ->fetchAll(PDO::FETCH_ASSOC);

    $this->view('invoice/create', compact('students','courses'));
  }

  public function store(){
    $this->requireRole('admin');
    $student_id = (int)($_POST['student_id'] ?? 0);
    $course_id  = (int)($_POST['course_id'] ?? 0);
    $amount     = (float)($_POST['amount'] ?? 0);
    $due_raw    = trim($_POST['due_date'] ?? '');

    if (!$student_id || $amount <= 0) {
      $this->flash('flash_error','Please select a student and enter a valid amount.');
      $this->redirect('index.php?url=invoice/create');
    }

    if ($due_raw === '') {
      $this->flash('flash_error','Please provide a due date.');
      $this->redirect('index.php?url=invoice/create');
    }

    $dueDate = DateTime::createFromFormat('Y-m-d', $due_raw);
    if (!$dueDate) {
      $this->flash('flash_error','Due date format is invalid.');
      $this->redirect('index.php?url=invoice/create');
    }

    $courseParam = $course_id > 0 ? $course_id : null;

    $stmt = $this->db->prepare("INSERT INTO invoices (student_id, course_id, amount, due_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'unpaid', NOW(), NOW())");
    $stmt->execute([$student_id, $courseParam, $amount, $dueDate->format('Y-m-d')]);

    $this->flash('flash_success','Invoice issued.');
    $this->redirect('index.php?url=invoice/index');
  }

  public function show($id){
    $this->requireRole(['admin','student']);
    @session_start();

    $stmt = $this->db->prepare("SELECT i.*, 
                                       CONCAT_WS(' ', u.first_name, u.last_name) AS student_name,
                                       u.email AS student_email,
                                       u.phone AS student_phone,
                                       c.name AS course_name
                                 FROM invoices i
                                 LEFT JOIN users u ON u.id = i.student_id
                                 LEFT JOIN courses c ON c.id = i.course_id
                                 WHERE i.id=?");
    $stmt->execute([(int)$id]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inv) {
      $this->flash('flash_error','Invoice not found.');
      $this->redirect('index.php?url=invoice/index');
    }

    if (($_SESSION['role'] ?? '') === 'student' && (int)$inv['student_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
      $this->flash('flash_error','Not your invoice.');
      $this->redirect('index.php?url=invoice/index');
    }

    $balance = ($inv['status'] === 'paid') ? 0 : (float)$inv['amount'];
    $isOverdue = false;

    if (!empty($inv['due_date'])) {
      try {
        $due = new DateTimeImmutable($inv['due_date']);
        $isOverdue = ($inv['status'] !== 'paid') && ($due < new DateTimeImmutable('today'));
      } catch (Exception $e) {
        $isOverdue = false;
      }
    }

    $canManage = ($_SESSION['role'] ?? '') === 'admin';

    $this->view('invoice/show', [
      'inv'        => $inv,
      'balance'    => $balance,
      'isOverdue'  => $isOverdue,
      'canManage'  => $canManage,
    ]);
  }

  public function markPaid($id){
    $this->requireRole('admin');
    $this->db->prepare("UPDATE invoices SET status='paid', paid_on=NOW(), updated_at=NOW() WHERE id=?")
             ->execute([(int)$id]);

    $this->flash('flash_success','Invoice marked as paid.');
    $this->redirect("index.php?url=invoice/show/$id");
  }

  public function exportCsv(){
    $this->requireRole('admin');

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="invoices.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Student', 'Course', 'Amount', 'Status', 'Created At', 'Due Date', 'Paid On']);

    $stmt = $this->db->query("SELECT i.id,
                                     CONCAT_WS(' ', u.first_name, u.last_name) AS student_name,
                                     c.name AS course_name,
                                     i.amount, i.status, i.created_at, i.due_date, i.paid_on
                              FROM invoices i
                              LEFT JOIN users u ON u.id = i.student_id
                              LEFT JOIN courses c ON c.id = i.course_id
                              ORDER BY i.created_at DESC");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      fputcsv($out, $row);
    }
    fclose($out);
    exit;
  }

  public function download($id){
    @session_start();
    $userId = $_SESSION['user_id'] ?? null;
    $role   = $_SESSION['role'] ?? '';

    $stmt = $this->db->prepare("SELECT i.id, i.student_id, i.amount, i.status, i.created_at, i.due_date, i.paid_on,
                                       CONCAT_WS(' ', u.first_name, u.last_name) AS student_name,
                                       u.email AS student_email,
                                       c.name AS course_name
                                FROM invoices i
                                LEFT JOIN users u ON u.id = i.student_id
                                LEFT JOIN courses c ON c.id = i.course_id
                                WHERE i.id=?");
    $stmt->execute([(int)$id]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inv) {
      die("Invoice not found");
    }

    if ($role === 'student' && (int)$inv['student_id'] !== (int)$userId) {
      die("Unauthorized");
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="invoice_'.$inv['id'].'.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Invoice ID','Student','Email','Course','Amount','Status','Created At','Due Date','Paid On']);
    fputcsv($out, [
      $inv['id'], $inv['student_name'], $inv['student_email'], $inv['course_name'],
      $inv['amount'], $inv['status'], $inv['created_at'], $inv['due_date'], $inv['paid_on']
    ]);
    fclose($out);
    exit;
  }

  public function destroy($id){
    $this->requireRole('admin');
    $this->db->prepare('DELETE FROM invoices WHERE id=?')->execute([(int)$id]);
    $this->flash('flash_success','Invoice deleted.');
    $this->redirect('index.php?url=invoice/index');
  }
}
