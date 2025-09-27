<?php @session_start(); ?>
<?php include __DIR__."/../layouts/header.php"; ?>

<div class="breadcrumb"><a href="index.php?url=branch/index">Branches</a> / Edit</div>

<div class="mb-2" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
  <h1 class="mb-1">Edit branch</h1>
  <a class="btn outline" href="index.php?url=branch/index">Back to branches</a>
</div>

<form method="POST" action="index.php?url=branch/update/<?= (int)$branch['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="name">Name</label>
      <input id="name" name="name" value="<?= htmlspecialchars($branch['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="field">
      <label for="phone">Phone</label>
      <input id="phone" name="phone" value="<?= htmlspecialchars($branch['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="field" style="grid-column: 1 / -1;">
      <label for="address">Address</label>
      <input id="address" name="address" value="<?= htmlspecialchars($branch['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
  </div>

  <div class="actions" style="display:flex; gap:8px; align-items:center;">
    <button class="btn primary" type="submit">Save</button>
    <a class="btn outline" href="index.php?url=branch/index">Cancel</a>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
      <a class="btn danger" href="index.php?url=branch/destroy/<?= (int)$branch['id']; ?>" onclick="return confirm('Delete this branch?');">Delete</a>
    <?php endif; ?>
  </div>
</form>

<?php include __DIR__."/../layouts/footer.php"; ?>
