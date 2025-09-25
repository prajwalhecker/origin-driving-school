<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Edit Student</h2>
<form method="POST" action="index.php?url=student/update/<?= (int)$student['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>First name</label><input name="first_name" value="<?= e($student['first_name']) ?>" required></div>
    <div class="field"><label>Last name</label><input name="last_name" value="<?= e($student['last_name']) ?>" required></div>
    <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($student['email']) ?>" required></div>
    <div class="field"><label>Phone</label><input name="phone" value="<?= e($student['phone']) ?>"></div>
  </div>
  <div class="actions"><button class="btn primary">Save</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>