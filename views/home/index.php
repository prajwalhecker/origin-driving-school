
<?php @session_start(); ?>
<!DOCTYPE html>
<html lang="en" class="<?php echo !empty($_SESSION['dark_mode']) ? 'dark' : ''; ?>">
<head>
  <meta charset="UTF-8">
  <title>Origin Driving School</title>
  <link rel="stylesheet" href="assets/css/main.css"> <!-- your global CSS -->
</head>
<body>

<section class="hero">
  <div class="hero-text">
    <span class="eyebrow">Origin Driving School Platform</span>
    <h1>Confidence for every journey you teach, manage, or take.</h1>
    <p>
      Your modern digital companion for every lesson, every branch, every drive. 
      From your very first booking to full branch operations—Origin brings clarity 
      and calm to every mile ahead.
    </p>
    <div class="hero-actions">
      <a class="btn primary" href="index.php?url=auth/register">Enroll as a Student</a>
      <a class="btn outline" href="index.php?url=auth/login">Portal Login</a>
    </div>
    <ul class="hero-highlights">
      <li><strong>Guided onboarding</strong><br>Set up every branch in days, not months.</li>
      <li><strong>Real-time insights</strong><br>Lesson utilisation, pass rates & fleet health.</li>
      <li><strong>Human support</strong><br>Specialists who know Aussie licensing.</li>
    </ul>
  </div>
  <div class="hero-visual">
    <img src="assets/images/hero-driving.jpg" alt="Driving School">
  </div>
</section>

<section class="stats-bar">
  <div>
    <span class="stat-number">3.2k</span>+
    <p class="stat-label">Graduated Drivers</p>
  </div>
  <div>
    <span class="stat-number">24</span>
    <p class="stat-label">Expert Instructors</p>
  </div>
  <div>
    <span class="stat-number">8</span>
    <p class="stat-label">Connected Branches</p>
  </div>
  <div>
    <span class="stat-number">100</span>%
    <p class="stat-label">Lesson Visibility</p>
  </div>
</section>

<section class="section value-prop">
  <header class="section-header" style="text-align:center; padding:20px;">
    <h2>Why schools choose Origin as their digital co-pilot</h2>
    <p>Balance structure and flexibility—so every learner gets consistent coaching while your team stays nimble.</p>
  </header>
  <div class="value-grid">
    <article class="value-card"><h3>Immersive student journeys</h3><p>Personalised roadmaps with milestones, reminders & resources.</p></article>
    <article class="value-card"><h3>Simplified team scheduling</h3><p>Balance instructor workloads & reduce scheduling gaps.</p></article>
    <article class="value-card"><h3>Secure by design</h3><p>Protect student data with compliance-first infrastructure.</p></article>
    <article class="value-card"><h3>Decisions backed by data</h3><p>Spot trends early with dashboards and weekly summaries.</p></article>
  </div>
</section>

<section class="testimonial">
  <blockquote>
    “Origin gives our students confidence before they even step into the car. The real-time view keeps our instructors aligned and our branches humming.”
  </blockquote>
  <cite style="display:block; margin-top:10px;">— Prajwal Khadka, Director</cite>
</section>

<section class="final-cta">
  <h2>Drive the future of safer roads</h2>
  <p>Join Origin today and empower every learner, instructor, and manager.</p>
  <div>
    <a class="btn primary" href="index.php?url=auth/register">Get started now</a>
    <a class="btn outline" href="index.php?url=course/index">View our courses</a>
  </div>
</section>

</body>
</html>
