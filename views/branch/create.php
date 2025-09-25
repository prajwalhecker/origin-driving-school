<div class="breadcrumb"><a href="index.php?url=branch/index">Branches</a> / New</div>
<div class="mb-2" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
  <div>
    <h1 class="mb-1">Add a branch</h1>
    <p class="muted mb-0">Create a new location that will appear on the public branches page.</p>
  </div>
  <a class="btn outline" href="index.php?url=branch/index">Back to branches</a>
</div>

<form method="POST" action="index.php?url=branch/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="name">Name</label>
      <input id="name" name="name" required>
    </div>
    <div class="field">
      <label for="phone">Phone</label>
      <input id="phone" name="phone">
    </div>
    <div class="field" style="grid-column: 1 / -1;">
      <label for="address">Address</label>
      <input id="address" name="address" required>
    </div>
  </div>
  <div class="actions">
    <button class="btn success" type="submit">Save branch</button>
    <a class="btn outline" href="index.php?url=branch/index">Cancel</a>
  </div>
</form>
