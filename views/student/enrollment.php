<?php
@session_start();

$profile = $profile ?? [];
$branches = $branches ?? [];
$courses = $courses ?? [];
$preferredDays = $preferredDays ?? [];

$currentBranchId = $profile['branch_id'] ?? $profile['user_branch_id'] ?? ($_SESSION['branch_id'] ?? null);
$currentCourseId = $profile['course_id'] ?? null;
$selectedCourseId = $selectedCourseId ?? null;
$selectedBranchId = $selectedBranchId ?? null;
if ($selectedCourseId) {
  $currentCourseId = $selectedCourseId;
}
if ($selectedBranchId) {
  $currentBranchId = $selectedBranchId;
}
$currentVehicle = $profile['vehicle_type'] ?? '';
$preferredTime = $profile['preferred_time'] ?? '';
$startDate = $profile['start_date'] ?? '';
$studentName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
$branchName = $profile['branch_name'] ?? ($_SESSION['branch_name'] ?? '');
$courseName = $profile['course_name'] ?? '';
$coursePrice = $profile['course_price'] ?? null;

$dayOptions = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
?>

<div class="breadcrumb">Student / Manage enrolment</div>
<div class="mb-3">
  <h1 class="mb-1">Manage your course enrolment</h1>
  <p class="muted mb-0">Switch branches, change courses, or update your lesson preferences. We'll keep your invoices and schedule aligned automatically.</p>
</div>

<div class="cards mb-3">
  <div class="card">
    <h4 class="mb-1">Current setup</h4>
    <p class="mb-0"><strong>Student:</strong> <?= htmlspecialchars($studentName ?: ($_SESSION['student_name'] ?? 'You')); ?></p>
    <p class="mb-0"><strong>Branch:</strong> <?= $branchName ? htmlspecialchars($branchName) : 'Not assigned'; ?></p>
    <p class="mb-0"><strong>Course:</strong> <?= $courseName ? htmlspecialchars($courseName) : 'Not enrolled'; ?></p>
    <?php if ($coursePrice !== null): ?>
      <p class="muted mb-0">Course fee: $<?= number_format((float)$coursePrice, 2); ?> · New invoices are generated on every enrolment.</p>
    <?php else: ?>
      <p class="muted mb-0">Select a course below to generate your invoice instantly.</p>
    <?php endif; ?>
  </div>
  <div class="card">
    <h4 class="mb-1">How it works</h4>
    <ul class="muted mb-0">
      <li>Updating your course issues a fresh invoice automatically.</li>
      <li>Your branch selection keeps schedules and fleet availability aligned.</li>
      <li>Preferred days & times guide instructors when allocating lessons.</li>
    </ul>
  </div>
</div>

<div class="card">
  <form method="post" action="index.php?url=student/enroll" class="form" style="display:grid; gap:16px;">
    <?php if (function_exists('csrf_field')) echo csrf_field(); ?>

    <div class="grid-2">
      <div class="field">
        <label for="branch_id">Branch</label>
        <select id="branch_id" name="branch_id" required>
          <option value="">Select branch</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= (int)$branch['id']; ?>" <?= (int)$currentBranchId === (int)$branch['id'] ? 'selected' : ''; ?>>
              <?= htmlspecialchars($branch['name'] ?? 'Branch'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="course_id">Course</label>
        <select id="course_id" name="course_id" required>
          <option value="">Select course</option>
          <?php foreach ($courses as $course): ?>
            <option value="<?= (int)$course['id']; ?>" <?= (int)$currentCourseId === (int)$course['id'] ? 'selected' : ''; ?>>
              <?= htmlspecialchars($course['name'] ?? 'Course'); ?> — $<?= number_format((float)($course['price'] ?? 0), 2); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label for="start_date">Preferred start date</label>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate); ?>">
        <small class="muted">Used to set your invoice due date. Leave blank if you're unsure.</small>
      </div>

      <div class="field">
        <label for="vehicle_type">Vehicle type</label>
        <select id="vehicle_type" name="vehicle_type">
          <option value="">Keep current</option>
          <option value="car" <?= $currentVehicle === 'car' ? 'selected' : ''; ?>>Car</option>
          <option value="motorcycle" <?= $currentVehicle === 'motorcycle' ? 'selected' : ''; ?>>Motorcycle</option>
        </select>
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label for="preferred_time">Preferred lesson time</label>
        <select id="preferred_time" name="preferred_time">
          <option value="" <?= $preferredTime === '' ? 'selected' : ''; ?>>No preference</option>
          <option value="Morning" <?= $preferredTime === 'Morning' ? 'selected' : ''; ?>>Morning</option>
          <option value="Afternoon" <?= $preferredTime === 'Afternoon' ? 'selected' : ''; ?>>Afternoon</option>
          <option value="Evening" <?= $preferredTime === 'Evening' ? 'selected' : ''; ?>>Evening</option>
        </select>
      </div>

      <div class="field">
        <label for="preferred_days">Preferred lesson days</label>
        <select id="preferred_days" name="preferred_days[]" multiple size="7">
          <?php foreach ($dayOptions as $day): ?>
            <option value="<?= $day; ?>" <?= in_array($day, $preferredDays, true) ? 'selected' : ''; ?>><?= $day; ?></option>
          <?php endforeach; ?>
        </select>
        <small class="muted">Hold Ctrl (Cmd on Mac) to choose multiple days.</small>
      </div>
    </div>

    <div class="actions" style="margin-top:8px;">
      <button type="submit" class="btn success">Save enrolment</button>
      <a href="index.php?url=student/dashboard" class="btn outline">Cancel</a>
    </div>
  </form>
</div>