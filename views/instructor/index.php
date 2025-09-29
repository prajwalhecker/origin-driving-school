<?php
@session_start();

$instructors = $instructors ?? [];
$currentRole = $_SESSION['role'] ?? null;
$isAdmin = $currentRole === 'admin';

if ($isAdmin) {
  $totalInstructors = count($instructors);
  $experienceYears = [];

  foreach ($instructors as $row) {
    if (!empty($row['experience']) && preg_match('/([0-9]+(?:\.[0-9]+)?)/', (string)$row['experience'], $matches)) {
      $experienceYears[] = (float)$matches[1];
    }
  }

  $avgExperience = null;
  if (!empty($experienceYears)) {
    $avgExperience = array_sum($experienceYears) / count($experienceYears);
  }

  $recentHire = $instructors[0] ?? null;
  $recentHireDate = null;
  if ($recentHire && !empty($recentHire['created_at'])) {
    $timestamp = strtotime($recentHire['created_at']);
    if ($timestamp) {
      $recentHireDate = date('d M Y', $timestamp);
    }
  }
}
?>

<?php if ($isAdmin): ?>
  <!-- Admin View -->
  <div class="breadcrumb">Team / Instructors</div>
  <div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div>
      <h1 class="mb-1">Instructor directory</h1>
      <p class="muted mb-0">Manage your coaching staff, their experience, and contact details.</p>
    </div>
    <a class="btn primary" href="index.php?url=instructor/create">+ Add instructor</a>
  </div>

  <?php if ($totalInstructors === 0): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No instructors on file</h3>
      <p class="muted mb-0">Add your first instructor to begin building your training team roster.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <div class="card">
        <h4 class="mb-1">Active instructors</h4>
        <div style="font-size:1.6rem;font-weight:700;"><?= $totalInstructors; ?></div>
        <p class="muted mb-0">Currently listed in your directory</p>
      </div>
      <div class="card">
        <h4 class="mb-1">Average experience</h4>
        <div style="font-size:1.6rem;font-weight:700; color:#b54708;">
          <?= $avgExperience !== null ? number_format($avgExperience, 1) . ' yrs' : '—'; ?>
        </div>
        <p class="muted mb-0">Based on reported experience</p>
      </div>
      <div class="card">
        <h4 class="mb-1">Newest addition</h4>
        <?php if ($recentHire): ?>
          <?php $recentName = $recentHire['full_name'] ?? $recentHire['name'] ?? '—'; ?>
          <div style="font-size:1.05rem;font-weight:600;">
            <?= htmlspecialchars($recentName); ?>
          </div>
          <p class="muted mb-0">
            <?= $recentHireDate ? 'Joined ' . htmlspecialchars($recentHireDate) : 'Recently added to your roster'; ?>
          </p>
        <?php else: ?>
          <p class="muted mb-0">Roster information will appear here</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Instructor</th>
            <th>Experience</th>
            <th>Address</th>
            <th>Phone</th>
            <th class="right">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($instructors as $inst): ?>
          <?php
            $instructorId = (int)($inst['id'] ?? 0);
            $displayName = $inst['full_name'] ?? $inst['name'] ?? 'Unnamed';
            $photoSrc = "assets/images/instructors/{$instructorId}.jpg";

            $searchIndex = strtolower(trim(implode(' ', array_filter([
              $displayName,
              $inst['experience'] ?? '',
              $inst['address'] ?? '',
              $inst['phone'] ?? '',
            ]))));
            $createdLabel = null;
            if (!empty($inst['created_at']) && ($time = strtotime($inst['created_at']))) {
              $createdLabel = 'Added ' . date('d M Y', $time);
            }
          ?>
          <tr data-instructor-row data-search="<?= htmlspecialchars($searchIndex); ?>">
            <td>
              <div class="stack">
                <img class="avatar" src="<?= htmlspecialchars($photoSrc); ?>" alt="<?= htmlspecialchars($displayName); ?>">
                <div class="stacked">
                  <strong><?= htmlspecialchars($displayName); ?></strong>
                  <?php if ($createdLabel): ?>
                    <span class="muted text-sm"><?= htmlspecialchars($createdLabel); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($inst['experience'] ?? '—'); ?></td>
            <td><div><?= htmlspecialchars($inst['address'] ?? '—'); ?></div></td>
            <td>
              <?php if (!empty($inst['phone'])): ?>
                <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $inst['phone'])); ?>"><?= htmlspecialchars($inst['phone']); ?></a>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>
            <td class="right actions">
              <a class="btn small outline" href="index.php?url=instructor/edit/<?= (int)$inst['id']; ?>">Edit</a>
              <a class="btn small danger" href="index.php?url=instructor/destroy/<?= (int)$inst['id']; ?>" onclick="return confirm('Delete this instructor?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php else: ?>
  <!-- Public View -->
  <div class="mb-3">
    <h1 class="mb-1">Meet our instructors</h1>
    <p class="muted mb-0">Experienced professionals ready to guide you safely onto the road.</p>
  </div>

  <?php if (empty($instructors)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">Instructor profiles coming soon</h3>
      <p class="muted mb-0">Check back later to meet the team of instructors who will support your journey.</p>
    </div>
  <?php else: ?>
    <div class="cards instructor-grid">
      <?php foreach ($instructors as $inst): ?>
        <?php
          $instructorId = (int)($inst['id'] ?? 0);
          $displayName  = $inst['full_name'] ?? $inst['name'] ?? 'Instructor';
          $photoSrc     = "assets/images/instructors/{$instructorId}.jpg";
        ?>
        <div class="card instructor-card">
          <img src="<?= htmlspecialchars($photoSrc); ?>" 
               alt="<?= htmlspecialchars($displayName); ?>" 
               class="mb-2 round" 
               style="height:160px; width:100%; object-fit:cover;">

          <div class="name"><?= htmlspecialchars($displayName); ?></div>
          <?php if (!empty($inst['experience'])): ?>
            <div class="meta"><?= htmlspecialchars($inst['experience']); ?></div>
          <?php endif; ?>
          <?php if (!empty($inst['phone'])): ?>
            <div class="meta">
              Call: <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $inst['phone'])); ?>">
                <?= htmlspecialchars($inst['phone']); ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?> <!-- end $isAdmin -->
