<!-- =========================
     FOOTER
========================= -->
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <h3>Origin Driving School</h3>
      <p>Confidence for every journey you teach, manage, or take.</p>
    </div>

   
    <div class="footer-meta">
      <p>&copy; <?= date('Y') ?> Origin Driving School. All rights reserved.</p>
      <p>
        <a href="index.php?url=auth/login">Login</a> | 
        <a href="index.php?url=auth/register">Register</a>
      </p>
    </div>
  </div>
</footer>

<!-- =========================
     FOOTER STYLES
========================= -->
<style>
.site-footer {
  background: var(--primary); /* same color as header */
  color: #fff;
  padding: 40px 20px;
  margin-top: 60px;
}

.footer-inner {
  max-width: 1200px;
  margin: auto;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 30px;
}

.footer-brand h3 {
  margin: 0 0 10px;
  font-size: 1.5rem;
}

.footer-links {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.footer-links a {
  color: #fff;
  text-decoration: none;
  transition: 0.3s;
}
.footer-links a:hover {
  text-decoration: underline;
}

.footer-meta {
  font-size: 0.9rem;
  opacity: 0.9;
}

.footer-meta a {
  color: #fff;
}
</style>
