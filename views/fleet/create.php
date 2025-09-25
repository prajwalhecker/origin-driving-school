<div class="breadcrumb"><a href="index.php?url=fleet/index">Fleet</a> / New Vehicle</div>
<div class="mb-2" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
  <div>
    <h1 class="mb-1">Add a vehicle</h1>
    <p class="muted mb-0">Register a training vehicle so it can be scheduled for lessons.</p>
  </div>
  <a class="btn outline" href="index.php?url=fleet/index">Back to fleet</a>
</div>

<form method="POST" action="index.php?url=fleet/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="plate">Registration number</label>
      <input id="plate" name="plate" required>
    </div>
    <div class="field">
      <label for="make">Make</label>
      <input id="make" name="make" required>
    </div>
    <div class="field">
      <label for="model">Model</label>
      <input id="model" name="model" required>
    </div>
  </div>
  <div class="actions">
    <button class="btn success" type="submit">Save vehicle</button>
    <a class="btn outline" href="index.php?url=fleet/index">Cancel</a>
  </div>
</form>
