<?php
@session_start();
$role = $role ?? ($_SESSION['role'] ?? 'guest');

// ✅ Use full name from session
$fullName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
if (!$fullName && !empty($_SESSION['student_name'])) {
  $fullName = $_SESSION['student_name']; // fallback
}
?>

<h2 class="mb-3">
  Welcome, <?= htmlspecialchars($fullName ?: 'Guest') ?>!
  <?php if ($role !== 'guest'): ?>
    <small style="color:#666;"></small>
  <?php endif; ?>
</h2>

<div class="dashboard-cards">
  <?php if ($role === 'admin'): ?>
    <div class="card"><h4>Students</h4><div class="stat"><?= (int)($totalStudents ?? 0) ?></div></div>
    <div class="card"><h4>Instructors</h4><div class="stat"><?= (int)($totalInstructors ?? 0) ?></div></div>
    <div class="card"><h4>Bookings</h4><div class="stat"><?= (int)($totalBookings ?? 0) ?></div></div>
    <div class="card"><h4>Income</h4><div class="stat">$<?= number_format((float)($income ?? 0),2) ?></div></div>

  <?php elseif ($role === 'student'): ?>
    <div class="card"><h4>Enrollments</h4><div class="stat"><?= (int)($enrollmentCount ?? 0) ?></div></div>
    <div class="card"><h4>Lessons Booked</h4><div class="stat"><?= (int)($lessonCount ?? 0) ?></div></div>
    <div class="card"><h4>Outstanding Balance</h4><div class="stat">$<?= number_format((float)($balance ?? 0),2) ?></div></div>

  <?php elseif ($role === 'instructor'): ?>
    <div class="card"><h4>Students Taught</h4><div class="stat"><?= (int)($studentsTaught ?? 0) ?></div></div>
    <div class="card"><h4>Completed Lessons</h4><div class="stat"><?= (int)($completedLessons ?? 0) ?></div></div>
    <div class="card"><h4>Upcoming Lessons</h4><div class="stat"><?= (int)($upcomingLessons ?? 0) ?></div></div>

  <?php else: ?>
    <p>You are currently not logged in. <a href="index.php?url=auth/login">Login</a> to access your dashboard.</p>
  <?php endif; ?>
</div>

<style>
.dashboard-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
}
.dashboard-cards .card {
  flex: 1;
  min-width: 200px;
  padding: 16px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  text-align: center;
}
.dashboard-cards .card h4 {
  margin: 0 0 8px;
  font-size: 1rem;
  color: #555;
}
.dashboard-cards .card .stat {
  font-size: 1.6rem;
  font-weight: bold;
  color: #222;
}
</style>
