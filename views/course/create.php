<div class="breadcrumb"><a href="index.php?url=course/index">Courses</a> / New</div>
<div class="mb-2" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
  <div>
    <h1 class="mb-1">Add a course</h1>
    <p class="muted mb-0">Outline the course details so it appears for prospective students.</p>
  </div>
  <a class="btn outline" href="index.php?url=course/index">Back to courses</a>
</div>

<form method="POST" action="index.php?url=course/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="name">Name</label>
      <input id="name" name="name" required>
    </div>

    <div class="field" style="grid-column: 1 / -1;">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4"></textarea>
    </div>

    <div class="field">
      <label for="price">Price</label>
      <input id="price" type="number" step="0.01" name="price" required>
    </div>

    <div class="field">
      <label for="class_count">Classes</label>
      <input id="class_count" type="number" name="class_count" min="1" required>
    </div>
  </div>

  <div class="actions">
    <button class="btn success" type="submit">Save course</button>
    <a class="btn outline" href="index.php?url=course/index">Cancel</a>
  </div>
</form>
