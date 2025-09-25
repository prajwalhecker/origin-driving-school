<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Edit Booking</h2>
<form method="POST" action="index.php?url=schedule/update/<?= (int)$booking['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label>Status</label>
      <?php $st = $booking['status'] ?? 'scheduled'; ?>
      <select name="status">
        <option value="scheduled" <?= $st==='scheduled'?'selected':''; ?>>scheduled</option>
        <option value="completed" <?= $st==='completed'?'selected':''; ?>>completed</option>
        <option value="cancelled" <?= $st==='cancelled'?'selected':''; ?>>cancelled</option>
      </select>
    </div>
  </div>
  <div class="actions"><button class="btn primary">Save</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>