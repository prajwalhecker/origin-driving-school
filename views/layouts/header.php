<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Origin Driving School</title>
  <link rel="stylesheet" href="assets/css/main.css" />
</head>
<body>
<div class="topbar">
  <div class="inner">
    <div class="brand">
      <a href="index.php?url=dashboard/index" style="color:#fff;text-decoration:none;">Origin Driving School</a>
    </div>
    <div class="nav">
      <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor')): ?>
        <a href="index.php?url=student/index">Students</a>
      <?php endif; ?>

      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <a href="index.php?url=student/profile">My Profile</a>
      <?php endif; ?>

      <a href="index.php?url=instructor/index">Instructors</a>
      <a href="index.php?url=schedule/index">Schedule</a>
      <a href="index.php?url=invoice/index">Invoices</a>
      <a href="index.php?url=fleet/index">Fleet</a>
      <a href="index.php?url=branch/index">Branches</a>
      <a href="index.php?url=course/index">Courses</a>

      <?php if (!empty($_SESSION['user_id'])): ?>
        <span style="opacity:.9;">Role: <?= htmlspecialchars($_SESSION['role'] ?? '') ?></span>
        <?php if (($_SESSION['role'] ?? '') === 'student' && !empty($_SESSION['branch_name'])): ?>
          <span class="badge" style="background:#1d4ed8; color:#fff;">Branch: <?= htmlspecialchars($_SESSION['branch_name']); ?></span>
        <?php endif; ?>
        <a href="index.php?url=auth/logout">Logout</a>
      <?php else: ?>
        <a href="index.php?url=auth/login">Login</a>
        <a href="index.php?url=auth/register">Learn With Us</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="container">
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
