</div>

<!-- =========================
     FOOTER
========================= -->
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <p class="footer-eyebrow">Driving excellence since 1998</p>
      <h3>Origin Driving School</h3>
      <p>Confidence for every journey you teach, manage, or take.</p>

      <div class="footer-social" aria-label="Social media links">
        <a href="https://www.facebook.com" target="_blank" rel="noreferrer">Facebook</a>
        <a href="https://www.instagram.com" target="_blank" rel="noreferrer">Instagram</a>
        <a href="https://www.youtube.com" target="_blank" rel="noreferrer">YouTube</a>
      </div>
    </div>

    <nav class="footer-links" aria-label="Quick links">
      <p class="footer-heading">Explore</p>
      <ul>
        <li><a href="index.php?url=home/index">Home</a></li>
        <li><a href="index.php?url=course/index">Courses</a></li>
        <li><a href="index.php?url=instructor/index">Instructors</a></li>
        <li><a href="index.php?url=job/apply">Careers</a></li>
      </ul>
    </nav>

    <div class="footer-links">
      <p class="footer-heading">Support</p>
      <ul>
        <li><a href="index.php?url=auth/login">Instructor Portal</a></li>
        <li><a href="index.php?url=auth/login">Student Portal</a></li>
        <li><a href="mailto:hello@origindrivingschool.com">Email support</a></li>
        <li><a href="tel:+611300000123">1300 000 123</a></li>
      </ul>
    </div>

    <div class="footer-contact">
      <p class="footer-heading">Visit</p>
      <address>
        45 Learner Lane<br />
        Melbourne, VIC 3000<br />
        Australia
      </address>
      <a class="footer-map" href="https://maps.google.com" target="_blank" rel="noreferrer">View on Google Maps</a>
    </div>
  </div>

  <div class="footer-bottom">
    <p class="footer-meta">&copy; <?= date('Y') ?> Origin Driving School. All rights reserved.</p>
    <div class="footer-auth-links">
      <a href="index.php?url=auth/login">Login</a>
      <span aria-hidden="true">•</span>
      <a href="index.php?url=auth/register">Register</a>
    </div>
  </div>
</footer>

<?php $mainJs = asset_url('js/main.js'); ?>
<script src="<?= e($mainJs) ?>" defer></script>
</body>
</html>
