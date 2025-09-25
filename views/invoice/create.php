<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Create Invoice</h2>
<form method="POST" action="index.php?url=invoice/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Student ID</label><input name="student_id" required></div>
    <div class="field"><label>Amount</label><input type="number" step="0.01" name="amount" required></div>
  </div>
  <div class="actions"><button class="btn success">Create</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>