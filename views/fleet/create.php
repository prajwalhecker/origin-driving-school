<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Add Vehicle</h2>
<form method="POST" action="index.php?url=fleet/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Plate</label><input name="plate" required></div>
    <div class="field"><label>Make</label><input name="make" required></div>
    <div class="field"><label>Model</label><input name="model" required></div>
  </div>
  <div class="actions"><button class="btn success">Save</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>