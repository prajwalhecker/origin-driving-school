<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Origin Driving School</title>
  <meta name="color-scheme" content="light dark">
  <script>
    (function () {
      try {
        var stored = window.localStorage.getItem('origin-theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = stored || (prefersDark ? 'dark' : 'light');
        document.documentElement.classList.add(theme === 'dark' ? 'theme-dark' : 'theme-light');
        document.documentElement.setAttribute('data-theme', theme);
      } catch (error) {
        document.documentElement.classList.add('theme-light');
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();
  </script>
  <?php $mainCss = asset_url('css/main.css'); ?>
  <link rel="stylesheet" href="<?= e($mainCss) ?>" />
</head>
<body>
<div class="topbar">
  <div class="inner">
    <div class="brand">
      <a href="index.php?url=dashboard/index" class="logo-link">
  <a href="index.php?url=dashboard/index" class="logo-link">
  <div class="logo-wrapper">
    <img src="assets/images/logo.png" alt="Origin Driving School" id="siteLogo">
  </div>
</a>

<style>
  .logo-wrapper {
    height: 50px;
    width: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  .logo-wrapper img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
    display: block;
  }
</style>


<script>
  document.addEventListener("DOMContentLoaded", () => {
    const logo = document.getElementById("siteLogo");
    if (logo) {
      logo.style.maxHeight = "50px";  
      logo.style.width = "auto";       
      logo.style.objectFit = "contain";
    }
  });
</script>

    </div>
    <div class="nav">
      <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor')): ?>
        <a href="index.php?url=student/index">Students</a>
      <?php endif; ?>

      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <a href="index.php?url=student/profile">My Profile</a>
      <?php endif; ?>

      <?php if (!isset($_SESSION['role']) || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'student'): ?>
    <a href="index.php?url=instructor/index">Instructors</a>
  <?php endif; ?>

      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="index.php?url=schedule/index">Schedule</a>
        <?php if (($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['role'] ?? '') === 'student'): ?>
          <a href="index.php?url=invoice/index">Invoices</a>
        <?php endif; ?>
      <?php endif; ?>

      <a href="index.php?url=fleet/index">Fleet</a>
      <a href="index.php?url=branch/index">Branches</a>
      <a href="index.php?url=course/index">Courses</a>

      <?php if (!empty($_SESSION['user_id'])): ?>
        <!-- User dropdown -->
        <div class="user-dropdown">
          <button class="user-btn">
            <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?> ▼
          </button>
          <div class="user-menu">
            <a href="index.php?url=auth/logout">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="index.php?url=auth/login">Login</a>
        <a href="index.php?url=auth/register">Learn With Us</a>
      <?php endif; ?>

      <!-- Dark mode toggle (unchanged) -->
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode">
        <span class="sr-only"></span>
        <span class="icon icon-sun" aria-hidden="true">☀️</span>
        <span class="icon icon-moon" aria-hidden="true">🌙</span>
      </button>
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
