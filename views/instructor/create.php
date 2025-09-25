<div class="breadcrumb"><a href="index.php?url=instructor/index">Instructors</a> / New</div>
<div class="mb-2" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
  <div>
    <h1 class="mb-1">Create an instructor</h1>
    <p class="muted mb-0">Set up an instructor profile so they can be assigned to lessons.</p>
  </div>
  <a class="btn outline" href="index.php?url=instructor/index">Back to instructors</a>
</div>

<form method="POST" action="index.php?url=instructor/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="first_name">First name</label>
      <input id="first_name" name="first_name" required>
    </div>
    <div class="field">
      <label for="last_name">Last name</label>
      <input id="last_name" name="last_name" required>
    </div>
    <div class="field">
      <label for="email">Email</label>
      <input id="email" type="email" name="email" required>
    </div>
    <div class="field">
      <label for="phone">Phone</label>
      <input id="phone" name="phone">
    </div>
    <div class="field">
      <label for="password">Password (optional)</label>
      <input id="password" type="password" name="password">
    </div>
  </div>
  <div class="actions">
    <button class="btn success" type="submit">Create instructor</button>
    <a class="btn outline" href="index.php?url=instructor/index">Cancel</a>
  </div>
</form>
