

<?php
$filters = $filters ?? ['status' => 'all', 'window' => 'upcoming'];
$summary = $summary ?? ['total' => 0, 'booked' => 0, 'completed' => 0, 'cancelled' => 0, 'upcoming' => 0];

$statusOptions = [
  'all'       => 'All statuses',
  'booked'    => 'Booked',
  'completed' => 'Completed',
  'cancelled' => 'Cancelled',
];

$windowOptions = [
  'upcoming' => 'Upcoming only',
  'all'      => 'Entire history',
  'past'     => 'Past lessons',
];

$role = $_SESSION['role'] ?? null;
$canManage = in_array($role, ['admin', 'instructor'], true);
$bookings = $bookings ?? [];
$nextBooking = $nextBooking ?? null;

$badgeClass = function(string $status): string {
  return [
    'booked'    => 'badge yellow',
    'completed' => 'badge green',
    'cancelled' => 'badge red',
  ][$status] ?? 'badge gray';
};

$grouped = [];
foreach ($bookings as $booking) {
  $dateKey = date('Y-m-d', strtotime($booking['start_time'] ?? ''));
  $grouped[$dateKey][] = $booking;
}
?>

<div class="breadcrumb">Operations / Lesson schedule</div>
<div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div>
    <h1 class="mb-1">Lesson schedule</h1>
    <p class="muted mb-0">Track upcoming lessons, resolve clashes and keep instructors busy.</p>
  </div>
  <?php if ($canManage): ?>
    <a class="btn primary" href="index.php?url=schedule/create">+ Add booking</a>
  <?php endif; ?>
</div>

<div class="cards mb-3">
  <div class="card">
    <h4 class="mb-1">Active bookings</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)$summary['total']; ?>
    </div>
    <p class="muted mb-0">Total lessons in your view</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Upcoming this week</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)$summary['upcoming']; ?>
    </div>
    <p class="muted mb-0">Lessons scheduled from today forward</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Completed</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)$summary['completed']; ?>
    </div>
    <p class="muted mb-0">Successful lessons</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Cancellations</h4>
    <div style="font-size:1.6rem;font-weight:700; color:#842029;">
      <?= (int)$summary['cancelled']; ?>
    </div>
    <p class="muted mb-0">Monitor repeated drop-outs</p>
  </div>
</div>

<?php if ($nextBooking): ?>
  <div class="card mb-3" style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
    <div style="flex:1 1 240px;">
      <h3 class="mb-1">Next lesson</h3>
      <p class="mb-0"><strong><?= htmlspecialchars($nextBooking['student_name'] ?? ''); ?></strong> with
        <?= htmlspecialchars($nextBooking['instructor_name'] ?? ''); ?></p>
    </div>
    <div style="flex:1 1 200px;">
      <p class="mb-0 muted">Starts</p>
      <p class="mb-0"><?= date('D, d M Y · h:i A', strtotime($nextBooking['start_time'] ?? '')); ?></p>
    </div>
    <div style="flex:1 1 200px;">
      <p class="mb-0 muted">Course</p>
      <p class="mb-0"><?= htmlspecialchars($nextBooking['course_name'] ?? 'Not linked'); ?></p>
    </div>
    <div style="flex:1 1 120px;" class="right">
      <span class="<?= $badgeClass($nextBooking['status'] ?? 'booked'); ?>"><?= htmlspecialchars($nextBooking['status'] ?? 'booked'); ?></span>
    </div>
  </div>
<?php endif; ?>

<div class="card mb-3">
  <form method="get" action="index.php" class="grid-3" style="align-items:end; gap:16px;">
    <input type="hidden" name="url" value="schedule/index">
    <div class="field">
      <label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach ($statusOptions as $value => $label): ?>
          <option value="<?= htmlspecialchars($value); ?>" <?= $filters['status'] === $value ? 'selected' : ''; ?>><?= htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="window">Timeframe</label>
      <select id="window" name="window">
        <?php foreach ($windowOptions as $value => $label): ?>
          <option value="<?= htmlspecialchars($value); ?>" <?= $filters['window'] === $value ? 'selected' : ''; ?>><?= htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>&nbsp;</label>
      <div class="actions" style="margin-top:0;">
        <button class="btn primary" type="submit">Apply filters</button>
        <a class="btn outline" href="index.php?url=schedule/index">Reset</a>
      </div>
    </div>
  </form>
</div>

<?php if (empty($bookings)): ?>
  <div class="card">
    <h3 class="mb-1">No lessons found</h3>
    <p class="muted mb-0">Adjust the filters above or add a new booking to get started.</p>
  </div>
<?php else: ?>
  <?php foreach ($grouped as $date => $items): ?>
    <div class="card mb-2">
      <h3 class="mb-2" style="margin-bottom:12px;"><?= date('l, d F Y', strtotime($date)); ?></h3>
      <div class="table-wrapper">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>Time</th>
              <th>Student</th>
              <th>Instructor</th>
              <th>Course</th>
              <th>Branch</th>
              <th>Vehicle</th>
              <th>Status</th>
              <?php if ($canManage): ?>
                <th class="right">Actions</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $row): ?>
            <tr>
              <td>
                <?= date('h:i A', strtotime($row['start_time'] ?? '')); ?>
                – <?= date('h:i A', strtotime($row['end_time'] ?? '')); ?>
              </td>
              <td><?= htmlspecialchars($row['student_name'] ?? ''); ?></td>
              <td><?= htmlspecialchars($row['instructor_name'] ?? ''); ?></td>
              <td><?= htmlspecialchars($row['course_name'] ?? 'Not linked'); ?></td>
              <td><?= htmlspecialchars($row['branch_name'] ?? ''); ?></td>
              <td><?= htmlspecialchars($row['vehicle_reg'] ?? '—'); ?></td>
              <td>
                <span class="<?= $badgeClass($row['status'] ?? 'booked'); ?>"><?= htmlspecialchars($row['status'] ?? 'booked'); ?></span>
              </td>
              <?php if ($canManage): ?>
                <td class="right">
                  <a class="btn small outline" href="index.php?url=schedule/edit/<?= (int)$row['id']; ?>">Edit</a>
                  <a class="btn small danger" href="index.php?url=schedule/destroy/<?= (int)$row['id']; ?>" onclick="return confirm('Delete this booking?');">Delete</a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>


