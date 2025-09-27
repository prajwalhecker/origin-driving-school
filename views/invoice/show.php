<?php
$inv       = $inv ?? [];
$balance   = $balance ?? 0.0;
$isOverdue = $isOverdue ?? false;
$canManage = $canManage ?? false;

$formatMoney = function($value): string {
  return '$' . number_format((float)$value, 2);
};

$statusBadge = function(array $invoice, bool $isOverdue): string {
  $status = strtolower($invoice['status'] ?? 'unpaid');
  $classes = [
    'paid'   => 'badge green',
    'unpaid' => 'badge yellow',
  ];
  $class = $classes[$status] ?? 'badge gray';

  if ($isOverdue && $status !== 'paid') {
    $class = 'badge red';
  }

  return sprintf('<span class="%s">%s</span>', $class, htmlspecialchars(ucfirst($status)));
};
?>

<div class="breadcrumb">
  <a href="index.php?url=invoice/index">Invoices</a> / Invoice #<?= (int)$inv['id']; ?>
</div>

<div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <h1 class="mb-1">Invoice #<?= (int)$inv['id']; ?></h1>
  <div style="display:flex; gap:10px;">
    <?php if ($canManage && $inv['status'] !== 'paid'): ?>
      <a class="btn success" 
         href="index.php?url=invoice/markPaid/<?= (int)$inv['id']; ?>"
         onclick="return confirm('Confirm cash payment for this invoice?');">
         Mark as Paid
      </a>
    <?php endif; ?>
    <?php if (($_SESSION['role'] ?? '') === 'student'): ?>
      <a class="btn outline" href="index.php?url=invoice/download/<?= (int)$inv['id']; ?>">⬇ Download</a>
    <?php endif; ?>
    <?php if ($canManage): ?>
      <a class="btn danger" 
         href="index.php?url=invoice/destroy/<?= (int)$inv['id']; ?>"
         onclick="return confirm('Delete this invoice?');">
         Delete
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <h3 class="mb-2">Invoice Details</h3>
  <table class="table">
    <tr>
      <th>Student</th>
      <td><?= htmlspecialchars($inv['student_name'] ?? 'Unknown'); ?> (<?= htmlspecialchars($inv['student_email'] ?? ''); ?>)</td>
    </tr>
    <tr>
      <th>Course</th>
      <td><?= htmlspecialchars($inv['course_name'] ?? 'Not linked'); ?></td>
    </tr>
    <tr>
      <th>Amount</th>
      <td><?= $formatMoney($inv['amount']); ?></td>
    </tr>
    <tr>
      <th>Due Date</th>
      <td>
        <?php if (!empty($inv['due_date'])): ?>
          <?= date('d M Y', strtotime($inv['due_date'])); ?>
          <?php if ($isOverdue && $inv['status'] !== 'paid'): ?>
            · <span class="badge red">Overdue</span>
          <?php endif; ?>
        <?php else: ?>
          —
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <th>Status</th>
      <td>
        <?= $statusBadge($inv, $isOverdue); ?>
        <?php if ($inv['status'] === 'paid' && !empty($inv['paid_on'])): ?>
          · Paid on <?= date('d M Y', strtotime($inv['paid_on'])); ?>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <th>Balance</th>
      <td><?= $formatMoney($balance); ?></td>
    </tr>
  </table>
</div>
