<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Edit Vehicle</h2>
<form method="POST" action="index.php?url=fleet/update/<?= (int)$car['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Plate</label><input name="plate" value="<?= e($car['plate'] ?? $car['plate_no'] ?? '') ?>" required></div>
    <div class="field"><label>Make</label><input name="make" value="<?= e($car['make'] ?? '') ?>" required></div>
    <div class="field"><label>Model</label><input name="model" value="<?= e($car['model'] ?? '') ?>" required></div>
  </div>
  <div class="actions"><button class="btn primary">Save</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>