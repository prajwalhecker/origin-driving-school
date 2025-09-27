<h2 class="mb-2">Edit Course</h2>
<form method="POST" action="index.php?url=course/update/<?= (int)$course['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label>Name</label>
      <input name="name" value="<?= htmlspecialchars($course['name'] ?? $course['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="field" style="grid-column: 1 / -1;">
      <label>Description</label>
      <textarea name="description" rows="4"><?= htmlspecialchars($course['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="field">
      <label>Price</label>
      <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($course['price'] ?? $course['fee'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="field">
      <label>Classes</label>
      <input type="number" name="class_count" min="1" value="<?= htmlspecialchars($course['class_count'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
  </div>

  <div class="actions">
    <button class="btn primary">Save</button>
    <a class="btn outline" href="index.php?url=course/index">Cancel</a>
  </div>
</form>
