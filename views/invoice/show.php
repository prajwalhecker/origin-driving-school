<?php

$inv       = $inv ?? [];
$payments  = $payments ?? [];
$paidTotal = $paidTotal ?? 0.0;
$balance   = $balance ?? 0.0;
$isOverdue = $isOverdue ?? false;
$canManage = $canManage ?? false;

$formatMoney = function($value): string {
  return '$' . number_format((float)$value, 2);
};

$statusBadge = function(array $invoice, bool $isOverdue): string {
  $status = strtolower($invoice['status'] ?? 'pending');
  $classes = [
    'paid'     => 'badge green',
    'pending'  => 'badge yellow',
    'partial'  => 'badge gray',
  ];
  $class = $classes[$status] ?? 'badge gray';
  if ($isOverdue) {
    $class = 'badge red';
  }
  return sprintf('<span class="%s">%s</span>', $class, htmlspecialchars(ucfirst($status)));
};

$dueLabel = function(array $invoice, bool $isOverdue): string {
  if (empty($invoice['due_date'])) {
    return '—';
  }
  $label = date('d M Y', strtotime($invoice['due_date']));
  if ($isOverdue) {
    $label .= ' · overdue';
  }
  return $label;
};

$createdLabel = !empty($inv['created_at']) ? date('d M Y', strtotime($inv['created_at'])) : 'Not recorded';

?>

<div class="breadcrumb">Finance / Invoices / #<?= (int)($inv['id'] ?? 0); ?></div>
<div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div>
    <h1 class="mb-1">Invoice #<?= (int)($inv['id'] ?? 0); ?></h1>
    <p class="muted mb-0">Issued <?= htmlspecialchars($createdLabel); ?> <?= $inv['course_name'] ? ' · ' . htmlspecialchars($inv['course_name']) : ''; ?></p>
  </div>
  <div>
    <a class="btn outline" href="index.php?url=invoice/index">Back to invoices</a>
  </div>
</div>

<div class="cards mb-3">
  <div class="card">
    <h4 class="mb-1">Invoice amount</h4>
    <div style="font-size:1.5rem;font-weight:700;">
      <?= $formatMoney($inv['amount'] ?? 0); ?>
    </div>
  </div>
  <div class="card">
    <h4 class="mb-1">Payments received</h4>
    <div style="font-size:1.5rem;font-weight:700; color:#0b6b33;">
      <?= $formatMoney($paidTotal); ?>
    </div>
  </div>
  <div class="card">
    <h4 class="mb-1">Balance due</h4>
    <div style="font-size:1.5rem;font-weight:700; color:<?= $balance > 0 ? '#b54708' : '#0b6b33'; ?>;">
      <?= $formatMoney($balance); ?>
    </div>
    <p class="muted mb-0">Due <?= htmlspecialchars($dueLabel($inv, $isOverdue)); ?></p>
  </div>
  <div class="card">
    <h4 class="mb-1">Status</h4>
    <div><?= $statusBadge($inv, $isOverdue); ?></div>
  </div>
</div>

<div class="grid-2 mb-3" style="gap:16px;">
  <div class="card">
    <h3 class="mb-1">Student</h3>
    <p class="mb-0"><strong><?= htmlspecialchars($inv['student_name'] ?? 'Unknown student'); ?></strong></p>
    <?php if (!empty($inv['student_email'])): ?>
      <p class="mb-0 muted">Email: <?= htmlspecialchars($inv['student_email']); ?></p>
    <?php endif; ?>
    <?php if (!empty($inv['student_phone'])): ?>
      <p class="mb-0 muted">Phone: <?= htmlspecialchars($inv['student_phone']); ?></p>
    <?php endif; ?>
  </div>
  <div class="card">
    <h3 class="mb-1">Invoice details</h3>
    <p class="mb-0">Course: <?= htmlspecialchars($inv['course_name'] ?? 'Not linked'); ?></p>
    <p class="mb-0">Created: <?= htmlspecialchars($createdLabel); ?></p>
    <p class="mb-0">Due: <?= htmlspecialchars($dueLabel($inv, $isOverdue)); ?></p>
  </div>
</div>

<h2 class="mb-2">Payments</h2>

<?php if (empty($payments)): ?>
  <div class="card mb-3">
    <h3 class="mb-1">No payments recorded</h3>
    <p class="muted mb-0">Log payments as they come in to keep this invoice up to date.</p>
  </div>
<?php else: ?>
  <div class="table-wrapper mb-3">
    <table class="table">
      <thead>
        <tr>
          <th>Amount</th>
          <th>Method</th>
          <th>Payment date</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($payments as $payment): ?>
        <tr>
          <td><?= $formatMoney($payment['amount'] ?? 0); ?></td>
          <td><?= htmlspecialchars($payment['method'] ?? ''); ?></td>
          <td><?= !empty($payment['payment_date']) ? date('d M Y · h:i A', strtotime($payment['payment_date'])) : '—'; ?></td>
          <td><?= htmlspecialchars($payment['note'] ?? ''); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php if ($canManage): ?>
  <h2 class="mb-2">Record a payment</h2>
  <form method="POST" action="index.php?url=invoice/pay/<?= (int)($inv['id'] ?? 0); ?>" class="form mb-3">
    <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
    <div class="row">
      <div class="field">
        <label for="payment_amount">Amount received<span style="color:#dc3545;">*</span></label>
        <input id="payment_amount" name="amount" type="number" min="0" step="0.01" placeholder="0.00" required>
      </div>
      <div class="field">
        <label for="payment_method">Payment method</label>
        <select id="payment_method" name="method">
          <option value="cash">Cash</option>
          <option value="card">Card</option>
          <option value="bank_transfer">Bank transfer</option>
          <option value="online">Online</option>
          <option value="manual" selected>Manual entry</option>
        </select>
      </div>
      <div class="field">
        <label for="payment_note">Internal note</label>
        <textarea id="payment_note" name="note" placeholder="Optional details (reference number, receipt, etc.)"></textarea>
      </div>
    </div>
    <div class="actions">
      <button class="btn primary" type="submit">Record payment</button>
    </div>
  </form>
<?php endif; ?>
