<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Student Dashboard</h2>
<p><strong>Outstanding balance:</strong> $<?= number_format((float)($balance ?? 0), 2) ?></p>
<h4 class="mb-1">Upcoming lessons</h4>
<table class="table">
  <thead><tr><th>Start</th><th>End</th><th>Status</th></tr></thead>
  <tbody>
    <?php foreach (($next ?? []) as $b): ?>
    <tr>
      <td><?= e($b['start_datetime']) ?></td>
      <td><?= e($b['end_datetime']) ?></td>
      <td><?= e($b['status']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__."/../layouts/footer.php"; ?>