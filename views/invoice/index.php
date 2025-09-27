<?php

$invoices  = $invoices ?? [];
$metrics   = $metrics ?? ['total' => 0, 'paid' => 0, 'outstanding' => 0, 'overdue' => 0];
$isStudent = $isStudent ?? false;

$formatMoney = function($value): string {
  return '$' . number_format((float)$value, 2);
};

$statusBadge = function(array $invoice): string {
  $status = strtolower($invoice['status'] ?? 'pending');
  $classes = [
    'paid'     => 'badge green',
    'unpaid'   => 'badge yellow',
    'pending'  => 'badge yellow',
    'partial'  => 'badge gray',
  ];
  $class = $classes[$status] ?? 'badge gray';

  if (!empty($invoice['is_overdue']) && $status !== 'paid') {
    $class = 'badge red';
  }

  return sprintf('<span class="%s">%s</span>', $class, htmlspecialchars(ucfirst($status)));
};

$dueLabel = function(array $invoice): string {
  if (empty($invoice['due_date'])) {
    return '—';
  }
  $label = date('d M Y', strtotime($invoice['due_date']));
  if (!empty($invoice['is_overdue'])) {
    $label .= ' · overdue';
  }
  return $label;
};

?>

<div class="breadcrumb">Finance / Invoices</div>
<div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div>
    <h1 class="mb-1">Invoices</h1>
    <p class="muted mb-0">Issue, track, and reconcile student invoices from a single workspace.</p>
  </div>
  <div style="display:flex; gap:10px;">
    <?php if (!$isStudent): ?>
      <a class="btn primary" href="index.php?url=invoice/create">+ New invoice</a>
      <a class="btn success" href="index.php?url=invoice/exportCsv">⬇ Download CSV</a>
    <?php endif; ?>
  </div>
</div>

<div class="cards mb-3">
  <div class="card">
    <h4 class="mb-1">Total issued</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)$metrics['total']; ?>
    </div>
    <p class="muted mb-0">Invoices currently on record</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Outstanding balance</h4>
    <div style="font-size:1.6rem;font-weight:700; color:#b54708;">
      <?= $formatMoney($metrics['outstanding']); ?>
    </div>
    <p class="muted mb-0">Amount still to be collected</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Paid in full</h4>
    <div style="font-size:1.6rem;font-weight:700; color:#0b6b33;">
      <?= (int)$metrics['paid']; ?>
    </div>
    <p class="muted mb-0">Invoices marked as paid</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Overdue</h4>
    <div style="font-size:1.6rem;font-weight:700; color:#842029;">
      <?= (int)$metrics['overdue']; ?>
    </div>
    <p class="muted mb-0">Require follow-up today</p>
  </div>
</div>

<?php if (empty($invoices)): ?>
  <div class="card">
    <h3 class="mb-1">No invoices yet</h3>
    <p class="muted mb-0">When you issue invoices, they will appear here along with their payment progress.</p>
  </div>
<?php else: ?>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Invoice #</th>
          <?php if (!$isStudent): ?><th>Student</th><?php endif; ?>
          <th>Course</th>
          <th>Amount</th>
          <th>Paid</th>
          <th>Balance</th>
          <th>Due date</th>
          <th>Status</th>
          <th class="right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($invoices as $invoice): ?>
        <tr<?php if (!empty($invoice['is_overdue']) && $invoice['status'] !== 'paid'): ?> style="background:#fff5f5;"<?php endif; ?>>
          <td>#<?= (int)$invoice['id']; ?></td>
          <?php if (!$isStudent): ?>
            <td>
              <div><?= htmlspecialchars($invoice['student_name'] ?? 'Unknown'); ?></div>
              <div class="muted" style="font-size:.85rem;"><?= htmlspecialchars($invoice['student_email'] ?? ''); ?></div>
            </td>
          <?php endif; ?>
          <td><?= htmlspecialchars($invoice['course_name'] ?? 'Not linked'); ?></td>
          <td><?= $formatMoney($invoice['amount'] ?? 0); ?></td>
          <td><?= $formatMoney($invoice['paid_total'] ?? 0); ?></td>
          <td><?= $formatMoney($invoice['balance'] ?? 0); ?></td>
          <td><?= htmlspecialchars($dueLabel($invoice)); ?></td>
          <td><?= $statusBadge($invoice); ?></td>
          <td class="right">
            <a class="btn small outline" href="index.php?url=invoice/show/<?= (int)$invoice['id']; ?>">View</a>

            <?php if ($isStudent): ?>
              <a class="btn small success" href="index.php?url=invoice/download/<?= (int)$invoice['id']; ?>">⬇ Download</a>
            <?php else: ?>
              <?php if ($invoice['status'] !== 'paid'): ?>
                <a class="btn small success" 
                   href="index.php?url=invoice/markPaid/<?= (int)$invoice['id']; ?>"
                   onclick="return confirm('Confirm cash payment for this invoice?');">
                   Mark as Paid
                </a>
              <?php endif; ?>
              <a class="btn small danger" 
                 href="index.php?url=invoice/destroy/<?= (int)$invoice['id']; ?>" 
                 onclick="return confirm('Delete this invoice? Payments will also be removed.');">
                 Delete
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
