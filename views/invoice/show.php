<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Invoice #<?= (int)$inv['id'] ?></h2>
<div class="card mb-2">
  <p><strong>Student ID:</strong> <?= e($inv['student_id']) ?></p>
  <p><strong>Amount:</strong> $<?= number_format((float)$inv['amount'],2) ?></p>
  <p><strong>Status:</strong> <?= e($inv['status']) ?></p>
  <p><strong>Issued:</strong> <?= e($inv['issued_date']) ?> | <strong>Due:</strong> <?= e($inv['due_date']) ?></p>
</div>

<h3 class="mb-1">Payments</h3>
<table class="table mb-2">
  <thead><tr><th>Amount</th><th>Method</th><th>Paid At</th></tr></thead>
  <tbody>
  <?php foreach (($payments ?? []) as $p): ?>
    <tr>
      <td>$<?= number_format((float)$p['amount'],2) ?></td>
      <td><?= e($p['method']) ?></td>
      <td><?= e($p['paid_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<form method="POST" action="index.php?url=invoice/pay/<?= (int)$inv['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Amount</label><input type="number" step="0.01" name="amount" required></div>
  </div>
  <div class="actions"><button class="btn primary">Record Payment</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>