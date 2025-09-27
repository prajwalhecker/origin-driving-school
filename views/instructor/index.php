<?php
@session_start();

$instructors = $instructors ?? [];
$currentRole = $_SESSION['role'] ?? null;
$isAdmin = $currentRole === 'admin';

if ($isAdmin) {
  $totalInstructors = count($instructors);
  $withPhoto = 0;
  $experienceYears = [];

  foreach ($instructors as $row) {
    if (!empty($row['photo'])) {
      $withPhoto++;
    }
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
        <h4 class="mb-1">Profile completion</h4>
        <div style="font-size:1.6rem;font-weight:700; color:#0b6b33;"><?= $withPhoto; ?></div>
        <p class="muted mb-0">Have uploaded profile photos</p>
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

    <div class="card directory-toolbar mb-2">
      <div class="search-group">
        <label for="instructorSearch">Quick search</label>
        <input id="instructorSearch" type="search" placeholder="Search by name, experience, or phone" autocomplete="off">
      </div>
      <div class="directory-toolbar__meta">
        <div class="chip-group" role="group" aria-label="Profile filters">
          <button type="button" class="chip active" data-photo-filter="all"><span class="dot"></span>All</button>
          <button type="button" class="chip" data-photo-filter="with"><span class="dot"></span>With photo</button>
          <button type="button" class="chip" data-photo-filter="without"><span class="dot"></span>Missing photo</button>
        </div>
        <small id="instructorResultCount">Showing <?= $totalInstructors; ?> instructor<?= $totalInstructors === 1 ? '' : 's'; ?></small>
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
            $photoFile = $inst['photo'] ?? 'default.png';
            $hasPhoto = !empty($inst['photo']);
            $photoPath = 'uploads/instructors/' . ($photoFile !== '' ? $photoFile : 'default.png');
            $displayName = $inst['full_name'] ?? $inst['name'] ?? 'Unnamed';
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
          <tr data-instructor-row
              data-photo="<?= $hasPhoto ? 'with' : 'without'; ?>"
              data-search="<?= htmlspecialchars($searchIndex); ?>">
            <td>
              <div class="stack">
                <img class="avatar" src="<?= htmlspecialchars($photoPath); ?>" alt="<?= htmlspecialchars($displayName); ?>">
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
          <tr data-empty-row style="display:none;">
            <td colspan="5" class="center muted">No instructors match your filters.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <script>
      (function(){
        const searchInput = document.getElementById('instructorSearch');
        const chips = Array.from(document.querySelectorAll('[data-photo-filter]'));
        const rows = Array.from(document.querySelectorAll('[data-instructor-row]'));
        const emptyRow = document.querySelector('[data-empty-row]');
        const countLabel = document.getElementById('instructorResultCount');
        const total = rows.length;
        let activeFilter = 'all';

        if (!rows.length) return;

        const updateCount = (visible) => {
          if (!countLabel) return;
          const plural = visible === 1 ? '' : 's';
          if (visible === total) {
            countLabel.textContent = `Showing ${visible} instructor${plural}`;
          } else {
            countLabel.textContent = `Showing ${visible} of ${total} instructors`;
          }
        };

        const applyFilters = () => {
          const query = (searchInput?.value || '').trim().toLowerCase();
          let visible = 0;

          rows.forEach((row) => {
            const photoState = row.dataset.photo || 'without';
            const searchable = row.dataset.search || '';

            const matchesPhoto = activeFilter === 'all' || photoState === activeFilter;
            const matchesSearch = !query || searchable.includes(query);

            if (matchesPhoto && matchesSearch) {
              row.style.display = '';
              visible++;
            } else {
              row.style.display = 'none';
            }
          });

          if (emptyRow) {
            emptyRow.style.display = visible ? 'none' : '';
          }

          updateCount(visible);
        };

        searchInput?.addEventListener('input', applyFilters);

        chips.forEach((chip) => {
          chip.addEventListener('click', () => {
            activeFilter = chip.dataset.photoFilter || 'all';
            chips.forEach((c) => c.classList.toggle('active', c === chip));
            applyFilters();
          });
        });

        applyFilters();
      })();
    </script>
  <?php endif; ?> <!-- end $totalInstructors -->
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
          $photoFile = $inst['photo'] ?? 'default.png';
          $photoPath = 'assets/images/' . ($photoFile !== '' ? $photoFile : 'default.png');
          $displayName = $inst['full_name'] ?? $inst['name'] ?? 'Instructor';
        ?>
        <div class="card instructor-card">
          <img src="<?= htmlspecialchars($photoPath); ?>" alt="<?= htmlspecialchars($displayName); ?>" class="instructor-photo">
          <div class="name"><?= htmlspecialchars($displayName); ?></div>
          <?php if (!empty($inst['experience'])): ?>
            <div class="meta"><?= htmlspecialchars($inst['experience']); ?></div>
          <?php endif; ?>
          <?php if (!empty($inst['phone'])): ?>
            <div class="meta">Call: <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $inst['phone'])); ?>"><?= htmlspecialchars($inst['phone']); ?></a></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?> <!-- end $isAdmin -->
