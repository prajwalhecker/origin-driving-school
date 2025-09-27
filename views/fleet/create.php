<div class="breadcrumb"><a href="index.php?url=fleet/index">Fleet</a> / New</div>
<div class="mb-2" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
  <div>
    <h1 class="mb-1">Add vehicle</h1>
    <p class="muted mb-0">Register a new vehicle into your school’s fleet.</p>
  </div>
  <a class="btn outline" href="index.php?url=fleet/index">Back to fleet</a>
</div>

<form method="POST" action="index.php?url=fleet/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="branch_id">Branch</label>
      <select id="branch_id" name="branch_id" required>
        <option value="">Select branch</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?= (int)$b['id']; ?>"><?= htmlspecialchars($b['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="registration_number">Registration number</label>
      <input id="registration_number" name="registration_number" required>
    </div>

    <div class="field">
      <label for="make">Make</label>
      <input id="make" name="make" required>
    </div>

    <div class="field">
      <label for="model">Model</label>
      <input id="model" name="model" required>
    </div>

    <div class="field">
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="available">Available</option>
        <option value="assigned">Assigned</option>
        <option value="maintenance">Maintenance</option>
      </select>
    </div>

    <div class="field">
      <label for="last_maintenance">Last maintenance</label>
      <input id="last_maintenance" type="date" name="last_maintenance" value="<?= date('Y-m-d'); ?>">
    </div>
  </div>

  <div class="actions">
    <button class="btn success" type="submit">Save vehicle</button>
    <a class="btn outline" href="index.php?url=fleet/index">Cancel</a>
  </div>
</form>
