

<div class="breadcrumb"><a href="index.php?url=schedule/index">Schedule</a> / New Booking</div>
<div class="mb-2" style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
  <h1 class="mb-0">Create lesson booking</h1>
  <a class="btn outline" href="index.php?url=schedule/index">Back to schedule</a>
</div>

<form method="POST" action="index.php" class="form">
  <input type="hidden" name="url" value="schedule/store">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="student_id">Student</label>
      <select id="student_id" name="student_id" required>
        <option value="" disabled selected>Select a student</option>
        <?php foreach (($students ?? []) as $student): ?>
          <option value="<?= (int)$student['id']; ?>">
            <?= htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="instructor_id">Instructor</label>
      <select id="instructor_id" name="instructor_id" required>
        <option value="" disabled selected>Select an instructor</option>
        <?php foreach (($instructors ?? []) as $instructor): ?>
          <option value="<?= (int)$instructor['id']; ?>"
            <?= (isset($_SESSION['role']) && $_SESSION['role'] === 'instructor' && (int)$instructor['id'] === (int)($_SESSION['user_id'] ?? 0)) ? 'selected' : ''; ?>>
            <?= htmlspecialchars(trim(($instructor['first_name'] ?? '') . ' ' . ($instructor['last_name'] ?? ''))); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <span class="help">Only free time slots will be accepted.</span>
    </div>

    <div class="field">
      <label for="course_id">Course (optional)</label>
      <select id="course_id" name="course_id">
        <option value="">Not linked</option>
        <?php foreach (($courses ?? []) as $course): ?>
          <option value="<?= (int)$course['id']; ?>"><?= htmlspecialchars($course['name'] ?? ''); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="branch_id">Branch (optional)</label>
      <select id="branch_id" name="branch_id">
        <option value="">Select a branch</option>
        <?php foreach (($branches ?? []) as $branch): ?>
          <option value="<?= (int)$branch['id']; ?>"><?= htmlspecialchars($branch['name'] ?? ''); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="vehicle_id">Vehicle (optional)</label>
      <select id="vehicle_id" name="vehicle_id">
        <option value="">Not assigned</option>
        <?php foreach (($vehicles ?? []) as $vehicle): ?>
          <?php $label = trim(($vehicle['registration_number'] ?? '') . ' · ' . ($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '')); ?>
          <option value="<?= (int)$vehicle['id']; ?>"><?= htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="start_time">Start time</label>
      <input type="datetime-local" id="start_time" name="start_time" required>
    </div>

    <div class="field">
      <label for="end_time">End time</label>
      <input type="datetime-local" id="end_time" name="end_time" required>
    </div>
  </div>

  <div class="actions">
    <button class="btn success" type="submit">Save booking</button>
    <a class="btn outline" href="index.php?url=schedule/index">Cancel</a>
  </div>
</form>

