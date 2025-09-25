
<h2 class="mb-2">Add Branch</h2>
<form method="POST" action="index.php?url=branch/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Name</label><input name="name" required></div>
    <div class="field"><label>Phone</label><input name="phone"></div>
    <div class="field" style="grid-column: 1 / -1;"><label>Address</label><input name="address" required></div>
  </div>
  <div class="actions"><button class="btn success">Save</button></div>
</form>
