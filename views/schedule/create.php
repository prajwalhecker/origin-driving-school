<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Create Booking</h2>
<form method="POST" action="index.php?url=schedule/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field"><label>Student ID</label><input name="student_id" required></div>
    <div class="field"><label>Instructor ID</label><input name="instructor_id" required></div>
    <div class="field"><label>Start</label><input type="datetime-local" name="start_datetime" required></div>
    <div class="field"><label>End</label><input type="datetime-local" name="end_datetime" required></div>
  </div>
  <div class="actions"><button class="btn success">Create</button></div>
</form>
<?php include __DIR__."/../layouts/footer.php"; ?>