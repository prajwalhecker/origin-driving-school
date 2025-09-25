
<h2 class="mb-2">Admin Dashboard</h2>
<div class="cards">
  <div class="card"><h4>Students</h4><div class="mb-1" style="font-size:1.6rem;"><?= (int)($totalStudents ?? 0) ?></div></div>
  <div class="card"><h4>Instructors</h4><div class="mb-1" style="font-size:1.6rem;"><?= (int)($totalInstructors ?? 0) ?></div></div>
  <div class="card"><h4>Bookings</h4><div class="mb-1" style="font-size:1.6rem;"><?= (int)($totalBookings ?? 0) ?></div></div>
  <div class="card"><h4>Income</h4><div class="mb-1" style="font-size:1.6rem;">$<?= number_format((float)($income ?? 0), 2) ?></div></div>
</div>
