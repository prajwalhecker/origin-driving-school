<?php
@session_start();

$branches = $branches ?? [];
$role = $_SESSION['role'] ?? 'guest';
$isAdmin = $role === 'admin';
$isInstructor = $role === 'instructor';

if ($isAdmin) {
  $branchTotal = count($branches);
  $withPhone = 0;
  $withoutPhone = 0;

  foreach ($branches as $branch) {
    if (!empty($branch['phone'])) {
      $withPhone++;
    } else {
      $withoutPhone++;
    }
  }
}
?>

<?php if ($isAdmin): ?>
  <div class="breadcrumb">Operations / Branches</div>
  <div class="mb-3" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
    <div>
      <h1 class="mb-1">Branch management</h1>
      <p class="muted mb-0">Create, organise, and audit every Origin branch from a single workspace.</p>
    </div>
    <a href="index.php?url=branch/create" class="btn primary">+ Add branch</a>
  </div>

  <?php if ($branchTotal === 0): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No branches yet</h3>
      <p class="muted mb-0">Set up your first branch to begin scheduling classes and assigning instructors.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <div class="card stat-card">
        <h4 class="mb-1">Active branches</h4>
        <div class="value"><?= $branchTotal; ?></div>
        <p class="muted mb-0">Ready for scheduling and invoicing</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Contact coverage</h4>
        <div class="value" style="color:#0b6b33;"><?= $withPhone; ?></div>
        <p class="muted mb-0">Branches with direct phone numbers</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Follow up needed</h4>
        <div class="value" style="color:#b54708;"><?= $withoutPhone; ?></div>
        <p class="muted mb-0">Add contact info to streamline reminders</p>
      </div>
    </div>

    <div class="card page-toolbar mb-2">
      <div class="search-group">
        <label for="branchSearch">Quick search</label>
        <input id="branchSearch" type="search" placeholder="Search by branch, address, or contact" autocomplete="off">
      </div>
      <div class="page-toolbar__meta">
        <div class="chip-group" role="group" aria-label="Contact filters">
          <button type="button" class="chip active" data-phone-filter="all"><span class="dot"></span>All</button>
          <button type="button" class="chip" data-phone-filter="with"><span class="dot"></span>Has phone</button>
          <button type="button" class="chip" data-phone-filter="without"><span class="dot"></span>Missing phone</button>
        </div>
        <small id="branchResultCount">Showing <?= $branchTotal; ?> branch<?= $branchTotal === 1 ? '' : 'es'; ?></small>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Branch</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Workflow</th>
            <th class="right">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($branches as $branch): ?>
          <?php
            $hasPhone = !empty($branch['phone']);
            $searchable = strtolower(trim(implode(' ', array_filter([
              $branch['name'] ?? '',
              $branch['address'] ?? '',
              $branch['phone'] ?? '',
            ]))));
          ?>
          <tr data-branch-row
              data-phone="<?= $hasPhone ? 'with' : 'without'; ?>"
              data-search="<?= htmlspecialchars($searchable); ?>">
            <td><strong><?= htmlspecialchars($branch['name'] ?? 'Branch'); ?></strong></td>
            <td>
              <?php if ($hasPhone): ?>
                <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $branch['phone'])); ?>"><?= htmlspecialchars($branch['phone']); ?></a>
              <?php else: ?>
                <span class="muted">Add phone</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($branch['address'] ?? '—'); ?></td>
            <td class="workflow">
              <a class="link-pill" href="index.php?url=schedule/index">Scheduling</a>
              <a class="link-pill" href="index.php?url=student/index">Student records</a>
              <a class="link-pill" href="index.php?url=invoice/index">Invoices</a>
            </td>
            <td class="right actions">
              <a class="btn small outline" href="index.php?url=branch/edit/<?= (int)$branch['id']; ?>">Edit</a>
              <a class="btn small danger" href="index.php?url=branch/destroy/<?= (int)$branch['id']; ?>" onclick="return confirm('Delete this branch?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
          <tr data-empty-row style="display:none;">
            <td colspan="5" class="center muted">No branches match your filters.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <script>
      (function(){
        const searchInput = document.getElementById('branchSearch');
        const chips = Array.from(document.querySelectorAll('[data-phone-filter]'));
        const rows = Array.from(document.querySelectorAll('[data-branch-row]'));
        const emptyRow = document.querySelector('[data-empty-row]');
        const countLabel = document.getElementById('branchResultCount');
        const total = rows.length;
        let phoneFilter = 'all';

        if (!rows.length) return;

        const updateCount = (visible) => {
          if (!countLabel) return;
          const plural = visible === 1 ? '' : 'es';
          if (visible === total) {
            countLabel.textContent = `Showing ${visible} branch${plural}`;
          } else {
            countLabel.textContent = `Showing ${visible} of ${total} branches`;
          }
        };

        const applyFilters = () => {
          const query = (searchInput?.value || '').trim().toLowerCase();
          let visible = 0;

          rows.forEach((row) => {
            const phoneState = row.dataset.phone || 'without';
            const searchable = row.dataset.search || '';

            const matchesPhone = phoneFilter === 'all' || phoneState === phoneFilter;
            const matchesSearch = !query || searchable.includes(query);

            if (matchesPhone && matchesSearch) {
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
            phoneFilter = chip.dataset.phoneFilter || 'all';
            chips.forEach((c) => c.classList.toggle('active', c === chip));
            applyFilters();
          });
        });

        applyFilters();
      })();
    </script>
  <?php endif; ?>
<?php elseif ($isInstructor): ?>
  <div class="mb-3">
    <h1 class="mb-1">Branch coverage</h1>
    <p class="muted mb-0">Track which sites you support, review notes, and prepare for upcoming lessons.</p>
  </div>

  <?php if (empty($branches)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No branches assigned</h3>
      <p class="muted mb-0">Connect with an administrator to be linked with your primary training location.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <?php foreach ($branches as $branch): ?>
        <div class="card instructor-branch">
          <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
              <h3 class="mb-1"><?= htmlspecialchars($branch['name'] ?? 'Branch'); ?></h3>
              <p class="muted mb-1"><?= htmlspecialchars($branch['address'] ?? 'Address unavailable'); ?></p>
              <?php if (!empty($branch['phone'])): ?>
                <p class="mb-1"><strong>Front desk:</strong> <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $branch['phone'])); ?>"><?= htmlspecialchars($branch['phone']); ?></a></p>
              <?php else: ?>
                <p class="muted mb-1">Add a contact number to streamline class reminders.</p>
              <?php endif; ?>
            </div>
            <div class="role-callout">
              <span class="status-pill">Lesson logistics</span>
              <ul class="muted">
                <li>Sync your availability on the shared calendar</li>
                <li>Log lesson notes and upload attachments after each session</li>
                <li>Confirm vehicles before class to avoid double-booking</li>
              </ul>
            </div>
          </div>
          <div class="stack" style="justify-content:flex-start; flex-wrap:wrap; gap:8px; margin-top:12px;">
            <a class="btn small outline" href="index.php?url=schedule/index">View branch schedule</a>
            <a class="btn small outline" href="index.php?url=student/index">Student roster</a>
            <a class="btn small outline" href="index.php?url=fleet/index">Fleet availability</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php else: ?>
  <div class="mb-3">
    <h1 class="mb-1">Visit our branches</h1>
    <p class="muted mb-0">Choose the most convenient Origin Driving School branch to begin your journey. Every site offers modern classrooms, comfortable waiting lounges, and quick access to our training fleet.</p>
  </div>

  <?php if (empty($branches)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">Locations coming soon</h3>
      <p class="muted mb-0">We’re expanding across the region. Check back shortly for new classroom options.</p>
    </div>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($branches as $branch): ?>
        <div class="card">
          <img src="assets/images/branches/<?= (int)$branch['id']; ?>.jpg" alt="<?= htmlspecialchars($branch['name'] ?? 'Branch'); ?>" class="mb-2 round" style="height:160px; width:100%; object-fit:cover;">
          <h3 class="mb-1"><?= htmlspecialchars($branch['name'] ?? 'Branch'); ?></h3>
          <p class="muted mb-1"><?= htmlspecialchars($branch['address'] ?? 'Address available upon request'); ?></p>
          <?php if (!empty($branch['phone'])): ?>
            <p class="mb-2"><strong>Call:</strong> <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $branch['phone'])); ?>"><?= htmlspecialchars($branch['phone']); ?></a></p>
          <?php endif; ?>
          <div class="muted mb-2">
            <strong>What to expect:</strong>
            <ul>
              <li>Flexible class schedules with SMS reminders</li>
              <li>Dedicated student success team for notes & attachments</li>
              <li>Easy access to vehicles and instructors on site</li>
            </ul>
          </div>
          <a href="index.php?url=branch/bookTour/<?= (int)$branch['id']; ?>" class="btn success">Book a tour</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
