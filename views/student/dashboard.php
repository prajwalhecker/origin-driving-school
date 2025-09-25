<?php
@session_start();

$profile = $profile ?? [];
$upcoming = $upcoming ?? [];
$balance = (float)($balance ?? 0);
$latestInvoice = $latestInvoice ?? null;

$studentName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
if ($studentName === '') {
    $studentName = $_SESSION['student_name'] ?? 'there';
}

$branchName = $profile['branch_name'] ?? ($_SESSION['branch_name'] ?? null);
$courseName = $profile['course_name'] ?? null;
$coursePrice = $profile['course_price'] ?? null;
$classCount = $profile['course_classes'] ?? null;
$vehicleType = $profile['vehicle_type'] ?? null;
$startDate = $profile['start_date'] ?? null;
$preferredTime = $profile['preferred_time'] ?? '';
$preferredDays = $profile['preferred_days_list'] ?? [];

$formatDate = function ($date) {
    if (!$date) {
        return '—';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date('d M Y', $timestamp) : htmlspecialchars((string)$date);
};

$formatMoney = function ($value) {
    return '$' . number_format((float)$value, 2);
};

$invoiceStatusBadge = function ($invoice) {
    $status = strtolower($invoice['status'] ?? 'pending');
    $classes = [
        'paid'    => 'badge green',
        'pending' => 'badge yellow',
        'partial' => 'badge gray',
        'overdue' => 'badge red',
        'unpaid'  => 'badge red',
    ];
    $class = $classes[$status] ?? 'badge gray';
    return '<span class="' . $class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
};

$lessonStatusClass = function ($status) {
    $status = strtolower($status ?? 'booked');
    return [
        'booked'    => 'badge yellow',
        'scheduled' => 'badge yellow',
        'completed' => 'badge green',
        'cancelled' => 'badge red',
    ][$status] ?? 'badge gray';
};
?>

<div class="breadcrumb">Student / Dashboard</div>
<div class="mb-3">
  <h1 class="mb-1">Welcome back, <?= htmlspecialchars($studentName); ?>!</h1>
  <p class="muted mb-0">
    <?= $branchName ? 'You\'re learning with the ' . htmlspecialchars($branchName) . ' branch.' : 'Choose a branch to start scheduling lessons.' ?>
    <?php if ($courseName): ?>
      Current course: <strong><?= htmlspecialchars($courseName); ?></strong>.
    <?php else: ?>
      <a href="index.php?url=student/enrollment">Enroll in a course to unlock scheduling.</a>
    <?php endif; ?>
  </p>
</div>

<div class="cards mb-3">
  <div class="card">
    <h4 class="mb-1">Outstanding balance</h4>
    <div style="font-size:1.6rem;font-weight:700; color:#b54708;">
      <?= $formatMoney($balance); ?>
    </div>
    <p class="muted mb-0">Includes all unpaid and pending invoices.</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Next lesson</h4>
    <?php if (!empty($upcoming)): ?>
      <?php $nextLesson = $upcoming[0]; ?>
      <p class="mb-0"><strong><?= date('D, d M Y', strtotime($nextLesson['start_time'])); ?></strong></p>
      <p class="mb-0">Starts at <?= date('h:i A', strtotime($nextLesson['start_time'])); ?> with <?= htmlspecialchars($nextLesson['instructor_name'] ?? 'TBA'); ?></p>
    <?php else: ?>
      <p class="mb-0">No lessons booked yet.</p>
      <a class="btn small outline" href="index.php?url=schedule/index">View schedule</a>
    <?php endif; ?>
  </div>
  <div class="card">
    <h4 class="mb-1">Course progress</h4>
    <?php if ($courseName): ?>
      <p class="mb-0"><strong><?= htmlspecialchars($courseName); ?></strong></p>
      <p class="muted mb-0"><?= $classCount ? intval($classCount) . ' classes' : 'Flexible number of classes'; ?> • Vehicle: <?= htmlspecialchars($vehicleType ?: 'TBC'); ?></p>
    <?php else: ?>
      <p class="mb-0">Not enrolled in a course yet.</p>
      <a class="btn small outline" href="index.php?url=student/enrollment">Choose a course</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($latestInvoice): ?>
  <?php
    $invoiceBalance = $latestInvoice['balance'] ?? 0;
    $dueDate = $latestInvoice['due_date'] ?? null;
  ?>
  <div class="card mb-3" style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
    <div style="flex:1 1 200px;">
      <h3 class="mb-0">Latest invoice</h3>
      <p class="muted mb-0">Invoice #<?= (int)$latestInvoice['id']; ?> for <?= htmlspecialchars($latestInvoice['course_name'] ?? 'your course'); ?></p>
    </div>
    <div style="flex:1 1 160px;">
      <p class="muted mb-0">Due date</p>
      <p class="mb-0"><?= $formatDate($dueDate); ?></p>
    </div>
    <div style="flex:1 1 160px;">
      <p class="muted mb-0">Balance</p>
      <p class="mb-0" style="font-weight:600; color:#b54708;"><?= $formatMoney($invoiceBalance); ?></p>
    </div>
    <div style="flex:0 0 auto;">
      <?= $invoiceStatusBadge($latestInvoice); ?>
    </div>
    <div style="flex:1 1 200px;" class="right">
      <a class="btn outline" href="index.php?url=invoice/show/<?= (int)$latestInvoice['id']; ?>">View invoice</a>
    </div>
  </div>
<?php else: ?>
  <div class="card mb-3">
    <h3 class="mb-1">No invoices yet</h3>
    <p class="muted mb-0">Once you enrol in a course we will generate your invoices automatically.</p>
  </div>
<?php endif; ?>

<div class="card mb-3">
  <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
    <div>
      <h3 class="mb-1">Course preferences</h3>
      <p class="muted mb-0">Keep these up to date so instructors can allocate lessons that fit your schedule.</p>
    </div>
    <a class="btn outline" href="index.php?url=student/enrollment">Update enrolment</a>
  </div>
  <div class="grid-3" style="margin-top:16px;">
    <div>
      <p class="muted mb-0">Preferred start date</p>
      <p class="mb-0"><?= $formatDate($startDate); ?></p>
    </div>
    <div>
      <p class="muted mb-0">Preferred time</p>
      <p class="mb-0"><?= $preferredTime ? htmlspecialchars($preferredTime) : 'No preference'; ?></p>
    </div>
    <div>
      <p class="muted mb-0">Preferred days</p>
      <p class="mb-0">
        <?php if (!empty($preferredDays)): ?>
          <?= htmlspecialchars(implode(', ', $preferredDays)); ?>
        <?php else: ?>
          No preference
        <?php endif; ?>
      </p>
    </div>
  </div>
</div>

<div class="card">
  <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
    <div>
      <h3 class="mb-1">Upcoming lessons</h3>
      <p class="muted mb-0">Your next 10 lessons are listed here. Need changes? Contact your instructor or update your preferences.</p>
    </div>
    <a class="btn outline" href="index.php?url=schedule/index">Open full schedule</a>
  </div>

  <?php if (empty($upcoming)): ?>
    <div class="empty-state-card" style="margin-top:16px;">
      <h3 class="mb-1">No bookings yet</h3>
      <p class="muted mb-0">Once the team schedules your lessons they will appear here automatically.</p>
    </div>
  <?php else: ?>
    <div class="table-wrapper" style="margin-top:16px;">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Instructor</th>
            <th>Course</th>
            <th>Branch</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($upcoming as $lesson): ?>
          <tr>
            <td><?= date('D, d M Y', strtotime($lesson['start_time'])); ?></td>
            <td><?= date('h:i A', strtotime($lesson['start_time'])); ?> – <?= date('h:i A', strtotime($lesson['end_time'])); ?></td>
            <td><?= htmlspecialchars($lesson['instructor_name'] ?? 'TBA'); ?></td>
            <td><?= htmlspecialchars($lesson['course_name'] ?? 'Not linked'); ?></td>
            <td><?= htmlspecialchars($lesson['branch_name'] ?? ''); ?></td>
            <?php $status = $lesson['status'] ?? 'booked'; ?>
            <td><span class="<?= $lessonStatusClass($status); ?>"><?= htmlspecialchars($status); ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
