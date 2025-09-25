<?php include __DIR__."/../layouts/header.php"; ?>
<h2 class="mb-2">Student Profile</h2>
<div class="card">
  <p><strong>Name:</strong> <?= e($student['first_name'].' '.$student['last_name']) ?></p>
  <p><strong>Email:</strong> <?= e($student['email']) ?></p>
  <p><strong>Phone:</strong> <?= e($student['phone']) ?></p>
</div>
<?php include __DIR__."/../layouts/footer.php"; ?>