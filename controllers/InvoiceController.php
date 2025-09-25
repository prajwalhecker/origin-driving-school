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
       c.name AS course_name,
       (
         SELECT COALESCE(SUM(amount),0)
         FROM payments p
         WHERE p.invoice_id = i.id
       ) AS paid_total
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
      $row['amount']     = (float)($row['amount'] ?? 0);
      $row['paid_total'] = (float)($row['paid_total'] ?? 0);
      $row['balance']    = max($row['amount'] - $row['paid_total'], 0);
      $row['is_overdue'] = false;

      if (!empty($row['due_date'])) {
        try {
          $dueDate = new DateTimeImmutable($row['due_date']);
          $row['is_overdue'] = ($row['balance'] > 0) && ($dueDate < $today);
        } catch (Exception $e) {
          $row['is_overdue'] = false;
        }
      }

      if (($row['status'] ?? '') === 'paid') {
        $metrics['paid']++;
      }
      if ($row['balance'] > 0) {
        $metrics['outstanding'] += $row['balance'];
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
    $status     = $_POST['status'] ?? 'pending';

    $validStatuses = ['pending','partial','paid'];
    if (!in_array($status, $validStatuses, true)) {
      $status = 'pending';
    }

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

    $stmt = $this->db->prepare("INSERT INTO invoices (student_id, course_id, amount, due_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$student_id, $courseParam, $amount, $dueDate->format('Y-m-d'), $status]);

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

    $pay = $this->db->prepare("SELECT id, amount, method, payment_date, note FROM payments WHERE invoice_id=? ORDER BY payment_date DESC, id DESC");
    $pay->execute([(int)$id]);
    $payments = $pay->fetchAll(PDO::FETCH_ASSOC);

    $paidTotal = 0.0;
    foreach ($payments as $payment) {
      $paidTotal += (float)($payment['amount'] ?? 0);
    }

    $balance = max((float)$inv['amount'] - $paidTotal, 0);
    $isOverdue = false;
    if (!empty($inv['due_date'])) {
      try {
        $due = new DateTimeImmutable($inv['due_date']);
        $isOverdue = ($balance > 0) && ($due < new DateTimeImmutable('today'));
      } catch (Exception $e) {
        $isOverdue = false;
      }
    }

    $canManage = ($_SESSION['role'] ?? '') === 'admin';

    $this->view('invoice/show', [
      'inv'        => $inv,
      'payments'   => $payments,
      'paidTotal'  => round($paidTotal, 2),
      'balance'    => round($balance, 2),
      'isOverdue'  => $isOverdue,
      'canManage'  => $canManage,
    ]);
  }

  public function pay($id){
    $this->requireRole('admin');
    $amount = (float)($_POST['amount'] ?? 0);
    $method = trim($_POST['method'] ?? 'manual');
    $note   = trim($_POST['note'] ?? '');

    if ($amount <= 0) {
      $this->flash('flash_error','Invalid amount.');
      $this->redirect("index.php?url=invoice/show/$id");
    }

    $invoiceStmt = $this->db->prepare('SELECT amount FROM invoices WHERE id=?');
    $invoiceStmt->execute([(int)$id]);
    $invoiceAmount = $invoiceStmt->fetchColumn();

    if ($invoiceAmount === false) {
      $this->flash('flash_error','Invoice not found.');
      $this->redirect('index.php?url=invoice/index');
    }

    $this->db->prepare('INSERT INTO payments (invoice_id, amount, payment_date, method, note) VALUES (?, ?, NOW(), ?, ?)')
             ->execute([(int)$id, $amount, $method ?: 'manual', $note !== '' ? $note : null]);

    $sumStmt = $this->db->prepare('SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id=?');
    $sumStmt->execute([(int)$id]);
    $paidTotal = (float)$sumStmt->fetchColumn();

    $status = 'pending';
    if ($paidTotal >= (float)$invoiceAmount - 0.01) {
      $status = 'paid';
    } elseif ($paidTotal > 0) {
      $status = 'partial';
    }

    $this->db->prepare('UPDATE invoices SET status=?, updated_at=NOW() WHERE id=?')
             ->execute([$status, (int)$id]);

    $this->flash('flash_success','Payment recorded.');
    $this->redirect("index.php?url=invoice/show/$id");
  }

  public function destroy($id){
    $this->requireRole('admin');
    $this->db->prepare('DELETE FROM payments WHERE invoice_id=?')->execute([(int)$id]);
    $this->db->prepare('DELETE FROM invoices WHERE id=?')->execute([(int)$id]);
    $this->flash('flash_success','Invoice deleted.');
    $this->redirect('index.php?url=invoice/index');
  }
}
