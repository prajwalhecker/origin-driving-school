<?php
class InvoiceController extends Controller {

  public function index(){
    $this->requireRole(['admin','student']);
    @session_start();
    if (($_SESSION['role'] ?? '') === 'student') {
      // show only their own invoices, newest first
      $stmt = $this->db->prepare("SELECT * FROM invoices WHERE student_id=? ORDER BY created_at DESC");
      $stmt->execute([$_SESSION['user_id']]);
    } else {
      // admin view → all invoices
      $stmt = $this->db->query("SELECT * FROM invoices ORDER BY created_at DESC");
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $this->view("invoice/index", ['invoices'=>$rows]);
  }

  public function create(){ 
    $this->requireRole('admin'); 
    $this->view("invoice/create"); 
  }

  public function store(){
    $this->requireRole('admin');
    $student_id = (int)($_POST['student_id'] ?? 0); 
    $amount     = (float)($_POST['amount'] ?? 0);

    if (!$student_id || $amount <= 0){ 
      $this->flash('flash_error','Invalid form.'); 
      return $this->view("invoice/create"); 
    }

    // insert new invoice → due in 14 days
    $stmt = $this->db->prepare("INSERT INTO invoices 
      (student_id, amount, due_date, status, created_at, updated_at)
      VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'pending', NOW(), NOW())");
    $stmt->execute([$student_id,$amount]);

    $this->flash('flash_success','Invoice issued.'); 
    $this->redirect("index.php?url=invoice/index");
  }

  public function show($id){
    $this->requireRole(['admin','student']);
    @session_start();
    $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id=?");
    $stmt->execute([(int)$id]); 
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inv) { 
      $this->flash('flash_error','Invoice not found.'); 
      $this->redirect("index.php?url=invoice/index"); 
    }

    if (($_SESSION['role']==='student') && $inv['student_id'] != $_SESSION['user_id']) {
      $this->flash('flash_error','Not your invoice.'); 
      $this->redirect("index.php?url=invoice/index");
    }

    $pay = $this->db->prepare("SELECT * FROM payments WHERE invoice_id=? ORDER BY paid_at DESC");
    $pay->execute([(int)$id]); 
    $payments = $pay->fetchAll(PDO::FETCH_ASSOC);

    $this->view("invoice/show", compact('inv','payments'));
  }

  public function pay($id){
    $this->requireRole(['admin','student']);
    $amount = (float)($_POST['amount'] ?? 0);

    if ($amount <= 0){ 
      $this->flash('flash_error','Invalid amount.'); 
      $this->redirect("index.php?url=invoice/show/$id"); 
    }

    $this->db->prepare("INSERT INTO payments (invoice_id, amount, method) VALUES (?,?, 'manual')")
             ->execute([(int)$id,$amount]);

    // Mark invoice as paid if fully covered
    $due = $this->db->prepare("SELECT amount - COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id=?),0) AS remain FROM invoices WHERE id=?");
    $due->execute([(int)$id,(int)$id]); 
    $remain = (float)$due->fetchColumn();

    if ($remain <= 0) {
      $this->db->prepare("UPDATE invoices SET status='paid', updated_at=NOW() WHERE id=?")
               ->execute([(int)$id]);
    }

    $this->flash('flash_success','Payment recorded.'); 
    $this->redirect("index.php?url=invoice/show/$id");
  }

  public function destroy($id){
    $this->requireRole('admin');
    $this->db->prepare("DELETE FROM payments WHERE invoice_id=?")->execute([(int)$id]);
    $this->db->prepare("DELETE FROM invoices WHERE id=?")->execute([(int)$id]);
    $this->flash('flash_success','Invoice deleted.'); 
    $this->redirect("index.php?url=invoice/index");
  }
}
