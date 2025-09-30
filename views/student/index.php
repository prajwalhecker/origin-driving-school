<?php
@session_start();

$students = $students ?? [];
$branches = $branches ?? [];
$filters = $filters ?? ['q' => '', 'branch' => 'all'];
$summary = $summary ?? ['total' => 0, 'recent' => 0, 'profiles' => 0, 'contactable' => 0, 'upcoming' => 0];
$isInstructorView = (bool)($isInstructorView ?? false);
$role = $_SESSION['role'] ?? '';
if ($role === 'instructor') {
    $isInstructorView = true;
}

usort($branches, static function ($a, $b) {
    return strcmp($a['name'] ?? '', $b['name'] ?? '');
});

$formatDate = static function (?string $value): string {
    if (!$value) {
        return '—';
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('d M Y', $timestamp) : '—';
};

$formatDateTime = static function (?string $value): string {
    if (!$value) {
        return '—';
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('d M Y • h:i A', $timestamp) : '—';
};
?>

<div class="breadcrumb"><?= $isInstructorView ? 'My learners' : 'People / Students'; ?></div>
<div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div>
    <h1 class="mb-1"><?= $isInstructorView ? 'Learners you are coaching' : 'Student directory'; ?></h1>
    <p class="muted mb-0">
      <?= $isInstructorView
        ? 'Review learner contact details, branches and the lesson load assigned to you.'
        : 'Manage learner records, launch enrolments, and keep contact details current.'; ?>
    </p>
  </div>
  <?php if (!$isInstructorView): ?>
    <a class="btn primary" href="index.php?url=student/create">+ Add student</a>
  <?php endif; ?>
</div>

<div class="cards mb-3">
  <div class="card">
    <h4 class="mb-1"><?= $isInstructorView ? 'Active learners' : 'Total learners'; ?></h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($summary['total'] ?? 0); ?>
    </div>
    <p class="muted mb-0">
      <?= $isInstructorView ? 'Learners with lessons assigned to you.' : 'All active student accounts'; ?>
    </p>
  </div>
  <div class="card">
    <h4 class="mb-1"><?= $isInstructorView ? 'Upcoming lessons' : 'Joined in last 30 days'; ?></h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($isInstructorView ? ($summary['upcoming'] ?? 0) : ($summary['recent'] ?? 0)); ?>
    </div>
    <p class="muted mb-0">
      <?= $isInstructorView
        ? 'Sessions still on your calendar across these learners.'
        : 'Track recent enrolments'; ?>
    </p>
  </div>
  <div class="card">
    <h4 class="mb-1">Profiles completed</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($summary['profiles'] ?? 0); ?>
    </div>
    <p class="muted mb-0">
      <?= $isInstructorView
        ? 'Learners who have provided scheduling preferences.'
        : 'Learners with onboarding details'; ?>
    </p>
  </div>
  <div class="card">
    <h4 class="mb-1">Contactable</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($summary['contactable'] ?? 0); ?>
    </div>
    <p class="muted mb-0">
      <?= $isInstructorView
        ? 'Learners with a phone number you can reach out to.'
        : 'Phone recorded for outreach'; ?>
    </p>
  </div>
</div>

<div class="card mb-3">
  <form method="get" action="index.php" class="grid-3" style="align-items:end; gap:16px;">
    <input type="hidden" name="url" value="student/index">
    <div class="field" style="grid-column:span 2;">
      <label for="student-search">Search</label>
      <input id="student-search" type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES); ?>" placeholder="<?= $isInstructorView ? 'Search your learners' : 'Search by name, email or phone'; ?>">
    </div>
    <div class="field">
      <label for="student-branch">Branch</label>
      <select id="student-branch" name="branch">
        <option value="all" <?= ($filters['branch'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All branches</option>
        <?php foreach ($branches as $branch): ?>
          <option value="<?= (int)($branch['id'] ?? 0); ?>" <?= (string)($filters['branch'] ?? '') === (string)($branch['id'] ?? '') ? 'selected' : ''; ?>>
            <?= htmlspecialchars($branch['name'] ?? ''); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>&nbsp;</label>
      <div class="actions" style="margin-top:0;">
        <button class="btn primary" type="submit">Apply filters</button>
        <a class="btn outline" href="index.php?url=student/index">Reset</a>
      </div>
    </div>
  </form>
</div>

<?php if (empty($students)): ?>
  <div class="card">
    <h3 class="mb-1">No students found</h3>
    <p class="muted mb-0">
      <?= $isInstructorView
        ? 'No learners are currently scheduled with you. Try adjusting the filters or check upcoming bookings.'
        : 'Try adjusting the filters above or add a new student profile.'; ?>
    </p>
  </div>
<?php else: ?>
  <div class="card">
    <div class="table-wrapper">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Branch</th>
            <?php if ($isInstructorView): ?>
              <th>Next lesson</th>
              <th>Lesson status</th>
            <?php else: ?>
              <th>Joined</th>
              <th class="right">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?></strong>
            </td>
            <td><?= htmlspecialchars($student['email'] ?? ''); ?></td>
            <td><?= htmlspecialchars($student['phone'] ?? '—'); ?></td>
            <td><?= htmlspecialchars($student['branch_name'] ?? 'Unassigned'); ?></td>
            <?php if ($isInstructorView): ?>
              <?php
                $nextLesson = $student['next_lesson_at'] ?? null;
                $upcomingLessons = (int)($student['upcoming_lessons'] ?? 0);
                $completedLessons = (int)($student['completed_lessons'] ?? 0);
              ?>
              <td>
                <?= $formatDateTime($nextLesson); ?>
                <?php if ($nextLesson && $upcomingLessons > 1): ?>
                  <div class="muted" style="font-size:0.85rem;">+<?= $upcomingLessons - 1; ?> more upcoming</div>
                <?php elseif (!$nextLesson && $upcomingLessons > 0): ?>
                  <div class="muted" style="font-size:0.85rem;">Lessons awaiting scheduling</div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($upcomingLessons > 0): ?>
                  <span class="badge yellow">Upcoming <?= $upcomingLessons; ?></span>
                <?php else: ?>
                  <span class="badge gray">No upcoming</span>
                <?php endif; ?>
                <div class="muted" style="font-size:0.85rem; margin-top:4px;">Completed <?= $completedLessons; ?></div>
              </td>
            <?php else: ?>
              <td><?= $formatDate($student['created_at'] ?? null); ?></td>
              <td class="right" style="white-space:nowrap;">
                <a class="btn link" href="index.php?url=student/show&id=<?= (int)($student['id'] ?? 0); ?>">View</a>
                <a class="btn link" href="index.php?url=student/edit&id=<?= (int)($student['id'] ?? 0); ?>">Edit</a>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
