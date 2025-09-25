<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Add Course</h2>
<form method="POST" action="index.php?url=course/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Name</label><input name="name" required></div>
    <div class="field" style="grid-column: 1 / -1;"><label>Description</label><textarea name="description" rows="4"></textarea></div>
    <div class="field"><label>Price</label><input type="number" step="0.01" name="price" required></div>
  </div>
  <div class="actions"><button class="btn success">Save</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>