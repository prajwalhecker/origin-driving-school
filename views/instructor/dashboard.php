<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Instructor Dashboard</h2>
<table class="table">
  <thead><tr><th>Start</th><th>End</th><th>Student</th><th>Status</th></tr></thead>
  <tbody>
    <?php foreach (($nextBookings ?? []) as $b): ?>
    <tr>
      <td><?= e($b['start_datetime']) ?></td>
      <td><?= e($b['end_datetime']) ?></td>
      <td><?= e(($b['first_name'] ?? '').' '.($b['last_name'] ?? '')) ?></td>
      <td><?= e($b['status'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__."/../layouts/footer.php"; ?>