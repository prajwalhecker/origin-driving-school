
<h2 class="mb-2">Login</h2>
<form method="POST" action="index.php?url=auth/login" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="email_or_phone">Email or Phone:</label>
<input type="text" name="email_or_phone" id="email_or_phone" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
  </div>
  <div class="actions">
    <button class="btn primary">Login</button>
  </div>
</form>
<p><a href="../views/auth/forgot.php">Forgot Password?</a></p>
