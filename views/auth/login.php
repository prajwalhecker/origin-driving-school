
<section class="auth-page" data-animate>
  <div class="auth-shell">
    <div class="auth-grid">
      <div class="auth-intro" data-animate>
        <span class="eyebrow">Back on the road</span>
        <h1>Welcome back to Origin Driving School</h1>
        <p>
          Sign in to access your personalised driving roadmap, manage bookings, and
          keep pace with your instructor&rsquo;s feedback &mdash; all in one polished dashboard.
        </p>
        <ul class="auth-benefits">
          <li>
            <span class="auth-benefits__icon" aria-hidden="true">🚗</span>
            <div>
              <h3>Clear progress tracking</h3>
              <p>Review lesson milestones and next steps without digging through emails.</p>
            </div>
          </li>
          <li>
            <span class="auth-benefits__icon" aria-hidden="true">🕒</span>
            <div>
              <h3>Flexible scheduling</h3>
              <p>Request, confirm, or adjust lessons in seconds with real-time updates.</p>
            </div>
          </li>
          <li>
            <span class="auth-benefits__icon" aria-hidden="true">🌙</span>
            <div>
              <h3>Theme that adapts</h3>
              <p>Dark mode keeps glare low for evening planning and late-night revisions.</p>
            </div>
          </li>
        </ul>
        <div class="auth-meta">
          <p class="auth-meta__title">New to Origin?</p>
          <a class="auth-meta__link" href="index.php?url=auth/register">Create a learner account</a>
        </div>
      </div>

      <div class="auth-card" data-animate="zoom">
        <div class="auth-card__header">
          <h2>Sign in securely</h2>
          <p>Enter your details to continue your journey.</p>
        </div>
        <form method="POST" action="index.php?url=auth/login" class="auth-form" autocomplete="on">
          <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
          <div class="auth-form__group">
            <label for="email_or_phone">Email or phone number</label>
            <input
              type="text"
              name="email_or_phone"
              id="email_or_phone"
              inputmode="email"
              autocomplete="username"
              value="<?= htmlspecialchars($_POST['email_or_phone'] ?? '') ?>"
              required
            >
          </div>
          <div class="auth-form__group">
            <div class="auth-form__label-row">
              <label for="password">Password</label>
              <a href="index.php?url=auth/forgot" class="auth-form__link">Forgot password?</a>
            </div>
            <input
              type="password"
              name="password"
              id="password"
              autocomplete="current-password"
              required
            >
          </div>
          <button type="submit" class="btn primary auth-form__submit">Sign in</button>
        </form>
        <div class="auth-card__footer">
          <p class="auth-trust">Trusted by thousands of learners preparing for their road test every month.</p>
          <div class="auth-support">
            <p>Need a hand? <a href="mailto:support@origindrive.com">support@origindrive.com</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
