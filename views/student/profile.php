<?php
$profile        = $profile ?? [];
$lessonStats    = $lessonStats ?? ['upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
$nextLesson     = $nextLesson ?? null;
$lastLesson     = $lastLesson ?? null;
$recentLessons  = $recentLessons ?? [];
$invoiceSummary = $invoiceSummary ?? ['total_invoices' => 0, 'outstanding_total' => 0, 'settled_total' => 0];
$latestInvoice  = $latestInvoice ?? null;

$studentName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
if ($studentName === '') {
  $studentName = 'there';
}

$courseName   = $profile['course_name'] ?? null;
$branchName   = $profile['branch_name'] ?? null;
$coursePrice  = $profile['course_price'] ?? null;
$courseLessons = $profile['course_classes'] ?? null;

$completedLessons = (int)($lessonStats['completed'] ?? 0);
$progressPercent = null;
if (!empty($courseLessons) && $courseLessons > 0) {
  $progressPercent = min(100, (int)round(($completedLessons / $courseLessons) * 100));
}

$preferredDays = $profile['preferred_days_list'] ?? [];

$formatDate = function ($date) {
  if (empty($date)) {
    return '—';
  }
  $timestamp = strtotime($date);
  return $timestamp ? date('d M Y', $timestamp) : htmlspecialchars((string)$date);
};

$formatDateTime = function ($dateTime) {
  if (empty($dateTime)) {
    return '—';
  }
  $timestamp = strtotime($dateTime);
  return $timestamp ? date('D, d M Y · h:i A', $timestamp) : htmlspecialchars((string)$dateTime);
};

$formatTimeRange = function ($start, $end = null) {
  if (empty($start)) {
    return '—';
  }
  $startTs = strtotime($start);
  $endTs = $end ? strtotime($end) : false;
  $startLabel = $startTs ? date('h:i A', $startTs) : htmlspecialchars((string)$start);
  if ($endTs) {
    $endLabel = date('h:i A', $endTs);
    return $startLabel . ' – ' . $endLabel;
  }
  return $startLabel;
};

$formatMoney = function ($value) {
  $number = is_numeric($value) ? (float)$value : 0;
  return '$' . number_format($number, 2);
};

$outstanding = (float)($invoiceSummary['outstanding_total'] ?? 0);
$settled     = (float)($invoiceSummary['settled_total'] ?? 0);
$totalInvoices = (int)($invoiceSummary['total_invoices'] ?? 0);

$nextLessonHeading = 'No upcoming lessons';
$nextLessonCopy = 'Lessons will appear here as soon as the team books them for you.';
if ($nextLesson) {
  $nextLessonHeading = 'Your next lesson';
  $nextLessonCopy = 'Be ready and arrive a few minutes early so you can jump straight in.';
} elseif ($lastLesson) {
  $nextLessonHeading = 'Last lesson recap';
  $nextLessonCopy = 'Here’s what you covered most recently—perfect for a quick refresher.';
}
?>

<div class="breadcrumb">Student / My profile</div>

<div class="card mb-3" style="display:flex; gap:24px; justify-content:space-between; align-items:flex-start; flex-wrap:wrap;">
  <div>
    <h1 class="mb-1">Hey <?= htmlspecialchars($studentName); ?>!</h1>
    <p class="muted mb-0">
      <?= $courseName
        ? 'You’re currently enrolled in <strong>' . htmlspecialchars($courseName) . '</strong>. '
        : 'Choose a course to unlock tailored lesson plans just for you.'; ?>
      <?= $branchName
        ? 'Training with the ' . htmlspecialchars($branchName) . ' branch.'
        : 'Let us know which branch suits you best to match instructors nearby.'; ?>
    </p>
  </div>
  <div style="display:flex; gap:12px; flex-wrap:wrap;">
    <?php if ($branchName): ?>
      <span class="badge gray">Branch · <?= htmlspecialchars($branchName); ?></span>
    <?php endif; ?>
    <?php if ($courseName && $coursePrice !== null): ?>
      <span class="badge yellow">Course fee · <?= $formatMoney($coursePrice); ?></span>
    <?php endif; ?>
  </div>
</div>

<div class="cards mb-3">
  <div class="card">
    <p class="muted mb-0">Upcoming lessons</p>
    <h3 class="mb-1" style="font-size:1.6rem;">
      <?= (int)($lessonStats['upcoming'] ?? 0) > 0 ? (int)$lessonStats['upcoming'] : '—'; ?>
    </h3>
    <p class="muted mb-0">
      <?= (int)($lessonStats['upcoming'] ?? 0) > 0
        ? 'Lessons already on the calendar.'
        : 'Nothing booked yet—keep an eye out!'; ?>
    </p>
  </div>
  <div class="card">
    <p class="muted mb-0">Lessons completed</p>
    <h3 class="mb-1" style="font-size:1.6rem;">
      <?= $completedLessons > 0 ? $completedLessons : '—'; ?>
    </h3>
    <p class="muted mb-0">
      <?= $completedLessons > 0
        ? 'Amazing work! Keep the momentum going.'
        : 'Your first lesson will appear here once it’s done.'; ?>
    </p>
  </div>
  <div class="card">
    <p class="muted mb-0">Course progress</p>
    <?php if ($progressPercent !== null): ?>
      <div style="margin:8px 0 12px; background:#f1f3f5; border-radius:999px; overflow:hidden; height:10px;">
        <div style="width:<?= $progressPercent; ?>%; background:#0d6efd; height:10px;"></div>
      </div>
      <p class="mb-0"><strong><?= $progressPercent; ?>%</strong> complete · <?= (int)$courseLessons; ?> classes total</p>
    <?php else: ?>
      <h3 class="mb-1" style="font-size:1.6rem;">—</h3>
      <p class="muted mb-0">We’ll calculate progress once your course includes a class count.</p>
    <?php endif; ?>
  </div>
  <div class="card">
    <p class="muted mb-0">Balance outstanding</p>
    <h3 class="mb-1" style="font-size:1.6rem; color:#b54708;">
      <?= $outstanding > 0 ? $formatMoney($outstanding) : '$0.00'; ?>
    </h3>
    <p class="muted mb-0">
      <?= $outstanding > 0
        ? 'Settle this to stay on track.'
        : 'All invoices are clear—nice and tidy!'; ?>
    </p>
  </div>
</div>

<div class="grid-2 mb-3" style="gap:24px;">
  <div class="card">
    <h3 class="mb-1">Profile snapshot</h3>
    <p class="muted mb-2">Here’s what your instructors see when they plan your lessons.</p>

    <div class="grid-2" style="gap:16px;">
      <div>
        <p class="muted mb-0">Email</p>
        <p class="mb-2"><?= htmlspecialchars($profile['email'] ?? '—'); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Phone</p>
        <p class="mb-2"><?= htmlspecialchars($profile['phone'] ?? '—'); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Licence type</p>
        <p class="mb-2"><?= htmlspecialchars($profile['licence_type'] ?? '—'); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Preferred vehicle</p>
        <p class="mb-2"><?= htmlspecialchars($profile['vehicle_type'] ?? '—'); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Home address</p>
        <p class="mb-0"><?= htmlspecialchars($profile['address'] ?? '—'); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Enrolled since</p>
        <p class="mb-0"><?= $formatDate($profile['enrolled_at'] ?? null); ?></p>
      </div>
    </div>
  </div>

  <div class="card">
    <h3 class="mb-1">Branch & preferences</h3>
    <p class="muted mb-2">We match your lessons around these preferences.</p>

    <div class="grid-2" style="gap:16px;">
      <div>
        <p class="muted mb-0">Branch</p>
        <p class="mb-2"><?= $branchName ? htmlspecialchars($branchName) : 'Not selected yet'; ?></p>
      </div>
      <div>
        <p class="muted mb-0">Branch phone</p>
        <p class="mb-2"><?= htmlspecialchars($profile['branch_phone'] ?? '—'); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Branch address</p>
        <p class="mb-2"><?= htmlspecialchars($profile['branch_address'] ?? '—'); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Preferred start date</p>
        <p class="mb-2"><?= $formatDate($profile['start_date'] ?? null); ?></p>
      </div>
      <div>
        <p class="muted mb-0">Preferred days</p>
        <p class="mb-2">
          <?= !empty($preferredDays)
            ? htmlspecialchars(implode(', ', $preferredDays))
            : 'No preference shared yet'; ?>
        </p>
      </div>
      <div>
        <p class="muted mb-0">Preferred time</p>
        <p class="mb-0"><?= htmlspecialchars($profile['preferred_time'] ?? 'Whenever works'); ?></p>
      </div>
    </div>
  </div>
</div>

<div class="grid-2" style="gap:24px;">
  <div class="card">
    <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
      <div>
        <h3 class="mb-1"><?= htmlspecialchars($nextLessonHeading); ?></h3>
        <p class="muted mb-0"><?= htmlspecialchars($nextLessonCopy); ?></p>
      </div>
      <a class="btn outline" href="index.php?url=schedule/index">View schedule</a>
    </div>

    <?php if ($nextLesson): ?>
      <div class="card" style="margin-top:16px; background:#f8f9fa;">
        <p class="muted mb-0">Starts</p>
        <p class="mb-1" style="font-weight:600;">
          <?= $formatDateTime($nextLesson['start_time'] ?? null); ?>
        </p>
        <p class="mb-0">With <?= htmlspecialchars($nextLesson['instructor_name'] ?? 'TBC'); ?></p>
        <p class="mb-0 muted">
          <?= htmlspecialchars($nextLesson['course_name'] ?? 'Course TBC'); ?> ·
          <?= htmlspecialchars($nextLesson['branch_name'] ?? 'Branch TBC'); ?>
        </p>
      </div>
    <?php elseif ($lastLesson): ?>
      <div class="card" style="margin-top:16px; background:#f8f9fa;">
        <p class="muted mb-0">Took place</p>
        <p class="mb-1" style="font-weight:600;">
          <?= $formatDateTime($lastLesson['start_time'] ?? null); ?>
        </p>
        <p class="mb-0">With <?= htmlspecialchars($lastLesson['instructor_name'] ?? 'TBC'); ?></p>
        <p class="mb-0 muted">
          <?= htmlspecialchars($lastLesson['course_name'] ?? 'Course TBC'); ?> ·
          <?= htmlspecialchars($lastLesson['branch_name'] ?? 'Branch TBC'); ?> ·
          <?= htmlspecialchars(ucfirst($lastLesson['status'] ?? 'completed')); ?>
        </p>
      </div>
    <?php else: ?>
      <div class="empty-state-card" style="margin-top:16px;">
        <h4 class="mb-1">We’ll keep you posted</h4>
        <p class="muted mb-0">As soon as a lesson is booked you’ll get an email and it will appear here.</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
      <div>
        <h3 class="mb-1">Invoices & payments</h3>
        <p class="muted mb-0">Track what’s paid and what still needs attention.</p>
      </div>
      <a class="btn outline" href="index.php?url=invoice/index">Open invoices</a>
    </div>

    <div class="grid-2" style="gap:16px; margin-top:16px;">
      <div>
        <p class="muted mb-0">Invoices on file</p>
        <p class="mb-2"><?= $totalInvoices > 0 ? $totalInvoices : '—'; ?></p>
      </div>
      <div>
        <p class="muted mb-0">Paid so far</p>
        <p class="mb-2"><?= $settled > 0 ? $formatMoney($settled) : '$0.00'; ?></p>
      </div>
      <div>
        <p class="muted mb-0">Outstanding</p>
        <p class="mb-2" style="color:#b54708; font-weight:600;">
          <?= $outstanding > 0 ? $formatMoney($outstanding) : '$0.00'; ?>
        </p>
      </div>
      <div>
        <p class="muted mb-0">Latest invoice</p>
        <p class="mb-0">
          <?php if ($latestInvoice): ?>
            <a href="index.php?url=invoice/show/<?= (int)$latestInvoice['id']; ?>">
              #<?= (int)$latestInvoice['id']; ?> · <?= htmlspecialchars($latestInvoice['course_name'] ?? 'Course'); ?>
            </a>
          <?php else: ?>
            None issued yet
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:24px;">
  <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
    <div>
      <h3 class="mb-1">Recent lesson history</h3>
      <p class="muted mb-0">Keep tabs on what you’ve covered and who you trained with.</p>
    </div>
  </div>

  <?php if (empty($recentLessons)): ?>
    <div class="empty-state-card" style="margin-top:16px;">
      <h4 class="mb-1">No lessons recorded yet</h4>
      <p class="muted mb-0">Your completed lessons will appear here along with instructor notes.</p>
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
        <?php foreach ($recentLessons as $lesson): ?>
          <tr>
            <td><?= $formatDate($lesson['start_time'] ?? null); ?></td>
            <td><?= $formatTimeRange($lesson['start_time'] ?? null, $lesson['end_time'] ?? null); ?></td>
            <td><?= htmlspecialchars($lesson['instructor_name'] ?? '—'); ?></td>
            <td><?= htmlspecialchars($lesson['course_name'] ?? '—'); ?></td>
            <td><?= htmlspecialchars($lesson['branch_name'] ?? '—'); ?></td>
            <td><?= htmlspecialchars(ucfirst($lesson['status'] ?? 'booked')); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
        </div>