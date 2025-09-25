
<h2 class="mb-2">Create Student</h2>
<form method="POST" action="index.php?url=student/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>First name</label><input name="first_name" required></div>
    <div class="field"><label>Last name</label><input name="last_name" required></div>
    <div class="field"><label>Email</label><input type="email" name="email" required></div>
    <div class="field"><label>Phone</label><input name="phone"></div>
    <div class="field"><label>Password (optional)</label><input type="password" name="password"></div>
  </div>
  <div class="actions"><button class="btn success">Create</button></div>
</form>
