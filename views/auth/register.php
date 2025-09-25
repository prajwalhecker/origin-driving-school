

<h2 class="mb-2">Become a Student</h2>

<form method="POST" action="index.php?url=auth/register" class="form" onsubmit="return validateRegForm();">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>

  <!-- Personal details -->
  <div class="card mb-2">
    <h3 class="mb-1">Your details</h3>
    <div class="row">
      <div class="field">
        <label>First name</label>
        <input name="first_name" required>
      </div>
      <div class="field">
        <label>Last name</label>
        <input name="last_name" required>
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div class="field">
        <label>Phone (mobile)</label>
        <input type="tel" name="phone" placeholder="04XXXXXXXX" pattern="^0[2-9]\d{8}$" title="Enter an Australian phone like 04XXXXXXXX">
      </div>
      <div class="field" style="grid-column:1 / -1;">
        <label>Home address</label>
        <input name="address" placeholder="Street, suburb, state, postcode">
      </div>
    </div>
  </div>

  <!-- Account credentials -->
  <div class="card mb-2">
    <h3 class="mb-1">Create your account</h3>
    <div class="row">
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" id="pw" minlength="6" required>
      </div>
      <div class="field">
        <label>Confirm password</label>
        <input type="password" name="password_confirm" id="pw2" minlength="6" required>
        <small id="pwHint" class="muted">Passwords must match.</small>
      </div>
    </div>
  </div>

  <!-- Learning preferences -->
  <div class="card mb-2">
    <h3 class="mb-1">Learning preferences</h3>
    <div class="row">
      <div class="field">
        <label>Branch</label>
        <select name="branch_id" required>
          <option value="">Select a branch</option>
          <?php foreach (($branches ?? []) as $b): ?>
            <option value="<?=$b['id']?>"><?=htmlspecialchars($b['name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>

    <div class="field">
  <label for="vt">Vehicle type</label>
  <select id="vt" name="vehicle_type" required>
    <option value="">Select type</option>
    <option value="car">Car</option>
    <option value="motorcycle">Motorcycle</option>
  </select>
</div>

      <div class="field">
        <label>Course</label>
        <select name="course_id" required>
          <option value="">Select a course</option>
          <?php foreach (($courses ?? []) as $c): ?>
            <option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?> — $<?=number_format($c['price'],2)?> (<?=$c['class_count']?> classes)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Preferred start date</label>
        <input type="date" name="start_date">
      </div>

     <div class="field" style="grid-column:1 / -1;">
  <label for="pref-days">Preferred days (Ctrl/Cmd-click to select multiple)</label>
  <select id="pref-days" name="preferred_days[]" multiple size="7">
    <option value="Mon">Mon</option>
    <option value="Tue">Tue</option>
    <option value="Wed">Wed</option>
    <option value="Thu">Thu</option>
    <option value="Fri">Fri</option>
    <option value="Sat">Sat</option>
    <option value="Sun">Sun</option>
  </select>
</div>

      <div class="field">
        <label>Preferred time</label>
        <select name="preferred_time">
          <option value="">No preference</option>
          <option>Morning</option>
          <option>Afternoon</option>
          <option>Evening</option>
        </select>
      </div>
    </div>
  </div>



  <div class="actions">
    <button class="btn success" id="submitBtn">Create account</button>
  </div>
</form>

<script>
function validateRegForm(){
  var pw = document.getElementById('pw');
  var pw2 = document.getElementById('pw2');
  var hint = document.getElementById('pwHint');
  if (pw.value !== pw2.value) {
    hint.textContent = "Passwords do not match.";
    hint.style.color = "#e5534b";
    pw2.focus();
    return false;
  }
  return true;
}
document.getElementById('pw2').addEventListener('input', function(){
  var hint = document.getElementById('pwHint');
  if (this.value === document.getElementById('pw').value) {
    hint.textContent = "Passwords match ✔";
    hint.style.color = "#22c55e";
  } else {
    hint.textContent = "Passwords must match.";
    hint.style.color = "";
  }
});
</script>
