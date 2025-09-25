
<?php
$students = $students ?? [];
$branches = $branches ?? [];
$filters = $filters ?? ['q' => '', 'branch' => 'all'];
$summary = $summary ?? ['total' => 0, 'recent' => 0, 'profiles' => 0, 'contactable' => 0];

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
?>

<div class="breadcrumb">People / Students</div>
<div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div>
    <h1 class="mb-1">Student directory</h1>
    <p class="muted mb-0">Manage learner records, launch enrolments, and keep contact details current.</p>
  </div>
  <a class="btn primary" href="index.php?url=student/create">+ Add student</a>
</div>

<div class="cards mb-3">
  <div class="card">
    <h4 class="mb-1">Total learners</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($summary['total'] ?? 0); ?>
    </div>
    <p class="muted mb-0">All active student accounts</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Joined in last 30 days</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($summary['recent'] ?? 0); ?>
    </div>
    <p class="muted mb-0">Track recent enrolments</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Profiles completed</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($summary['profiles'] ?? 0); ?>
    </div>
    <p class="muted mb-0">Learners with onboarding details</p>
  </div>
  <div class="card">
    <h4 class="mb-1">Contactable</h4>
    <div style="font-size:1.6rem;font-weight:700;">
      <?= (int)($summary['contactable'] ?? 0); ?>
    </div>
    <p class="muted mb-0">Phone recorded for outreach</p>
  </div>
</div>

<div class="card mb-3">
  <form method="get" action="index.php" class="grid-3" style="align-items:end; gap:16px;">
    <input type="hidden" name="url" value="student/index">
    <div class="field" style="grid-column:span 2;">
      <label for="student-search">Search</label>
      <input id="student-search" type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES); ?>" placeholder="Search by name, email or phone">
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
    <p class="muted mb-0">Try adjusting the filters above or add a new student profile.</p>
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
            <th>Joined</th>
            <th class="right">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?></strong>
            </td>
            <td><?= htmlspecialchars($student['email'] ?? ''); ?></td>
            <td><?= htmlspecialchars($student['phone'] ?? '—'); ?></td>
            <td><?= htmlspecialchars($student['branch_name'] ?? 'Unassigned'); ?></td>
            <td><?= $formatDate($student['created_at'] ?? null); ?></td>
            <td class="right" style="white-space:nowrap;">
              <a class="btn link" href="index.php?url=student/show&id=<?= (int)($student['id'] ?? 0); ?>">View</a>
              <a class="btn link" href="index.php?url=student/edit&id=<?= (int)($student['id'] ?? 0); ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>


