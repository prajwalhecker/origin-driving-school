<?php
$form        = $form ?? [];
$profile     = $profile ?? [];
$branches    = $branches ?? [];
$courses     = $courses ?? [];
$dayOptions  = $dayOptions ?? [];
$timeOptions = $timeOptions ?? [];

$selectedBranch = $form['branch_id'] ?? ($profile['branch_id'] ?? ($student['branch_id'] ?? ''));
$selectedVehicle = $form['vehicle_type'] ?? ($profile['vehicle_type'] ?? '');
$selectedCourse = $form['course_id'] ?? ($profile['course_id'] ?? '');
$selectedTime = $form['preferred_time'] ?? ($profile['preferred_time'] ?? '');
$startDate = $form['start_date'] ?? ($profile['start_date'] ?? '');
$addressValue = $form['address'] ?? ($profile['address'] ?? '');

$selectedDays = [];
if (!empty($form)) {
  $selectedDays = isset($form['preferred_days']) ? (array)$form['preferred_days'] : [];
} else {
  $prefDays = $profile['preferred_days'] ?? '';
  $selectedDays = $prefDays ? array_map('trim', explode(',', $prefDays)) : [];
}
?>

<div class="breadcrumb"><a href="index.php?url=student/index">Students</a> / Edit</div>
<h1 class="mb-2">Edit Student</h1>
<form method="POST" action="index.php?url=student/update/<?= (int)$student['id'] ?>" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="card mb-2">
    <h2 class="mb-1">Contact details</h2>
    <div class="row">
      <div class="field">
        <label for="first_name">First name</label>
        <input id="first_name" name="first_name" value="<?= e($form['first_name'] ?? $student['first_name']) ?>" required>
      </div>
      <div class="field">
        <label for="last_name">Last name</label>
        <input id="last_name" name="last_name" value="<?= e($form['last_name'] ?? $student['last_name']) ?>" required>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?= e($form['email'] ?? $student['email']) ?>" required>
      </div>
      <div class="field">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" value="<?= e($form['phone'] ?? ($profile['phone'] ?? $student['phone'])) ?>">
      </div>
      <div class="field" style="grid-column:1 / -1;">
        <label for="address">Address</label>
        <input id="address" name="address" value="<?= e($addressValue) ?>" placeholder="Street, suburb, state, postcode">
      </div>
    </div>
  </div>

  <div class="card mb-2">
    <h2 class="mb-1">Enrolment</h2>
    <div class="row">
      <div class="field">
        <label for="branch_id">Branch</label>
        <select id="branch_id" name="branch_id" required>
          <option value="">Select a branch</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= (int)$branch['id'] ?>" <?= ((string)$selectedBranch === (string)$branch['id']) ? 'selected' : '' ?>>
              <?= e($branch['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="vehicle_type">Vehicle type</label>
        <select id="vehicle_type" name="vehicle_type" required>
          <option value="">Select type</option>
          <option value="car" <?= $selectedVehicle === 'car' ? 'selected' : '' ?>>Car</option>
          <option value="motorcycle" <?= $selectedVehicle === 'motorcycle' ? 'selected' : '' ?>>Motorcycle</option>
        </select>
      </div>
      <div class="field">
        <label for="course_id">Course</label>
        <select id="course_id" name="course_id" required>
          <option value="">Select a course</option>
          <?php foreach ($courses as $course): ?>
            <option value="<?= (int)$course['id'] ?>" <?= ((string)$selectedCourse === (string)$course['id']) ? 'selected' : '' ?>>
              <?= e($course['name']) ?><?php if (isset($course['price'], $course['class_count'])): ?> — $<?= number_format((float)$course['price'], 2) ?> (<?= (int)$course['class_count'] ?> classes)<?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="start_date">Preferred start date</label>
        <input id="start_date" type="date" name="start_date" value="<?= e($startDate) ?>">
      </div>
      <div class="field">
        <label for="preferred_time">Preferred time</label>
        <select id="preferred_time" name="preferred_time">
          <option value="">No preference</option>
          <?php foreach ($timeOptions as $option): ?>
            <option value="<?= e($option) ?>" <?= $selectedTime === $option ? 'selected' : '' ?>><?= e($option) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="card mb-2">
    <h2 class="mb-1">Schedule preferences</h2>
    <div class="field">
      <label for="preferred_days">Preferred days (Ctrl/Cmd-click to select multiple)</label>
      <select id="preferred_days" name="preferred_days[]" multiple size="7">
        <?php foreach ($dayOptions as $day): ?>
          <option value="<?= e($day) ?>" <?= in_array($day, $selectedDays, true) ? 'selected' : '' ?>><?= e($day) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="actions">
    <button class="btn primary" type="submit">Save changes</button>
    <a class="btn outline" href="index.php?url=student/index">Cancel</a>
  </div>
</form>
