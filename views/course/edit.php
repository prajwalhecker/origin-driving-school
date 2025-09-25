<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Edit Course</h2>
<form method="POST" action="index.php?url=course/update/<?= (int)$course['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Name</label><input name="name" value="<?= e($course['name'] ?? $course['title'] ?? '') ?>" required></div>
    <div class="field" style="grid-column: 1 / -1;"><label>Description</label><textarea name="description" rows="4"><?= e($course['description'] ?? '') ?></textarea></div>
    <div class="field"><label>Price</label><input type="number" step="0.01" name="price" value="<?= e($course['price'] ?? $course['fee'] ?? 0) ?>" required></div>
  </div>
  <div class="actions"><button class="btn primary">Save</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>