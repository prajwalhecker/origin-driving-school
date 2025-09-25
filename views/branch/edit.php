<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Edit Branch</h2>
<form method="POST" action="index.php?url=branch/update/<?= (int)$branch['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Name</label><input name="name" value="<?= e($branch['name']) ?>" required></div>
    <div class="field"><label>Phone</label><input name="phone" value="<?= e($branch['phone']) ?>"></div>
    <div class="field" style="grid-column: 1 / -1;"><label>Address</label><input name="address" value="<?= e($branch['address']) ?>" required></div>
  </div>
  <div class="actions"><button class="btn primary">Save</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>