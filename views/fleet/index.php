<?php
@session_start();

$fleet = $fleet ?? [];
$role = $_SESSION['role'] ?? 'guest';
$isAdmin = $role === 'admin';
$isInstructor = $role === 'instructor';

if ($isAdmin || $isInstructor) {
  $totalVehicles = count($fleet);
  $statusCounts = [
    'available' => 0,
    'assigned' => 0,
    'maintenance' => 0,
  ];
  $needsService = 0;

  foreach ($fleet as $car) {
    $status = strtolower($car['status'] ?? 'available');
    if (isset($statusCounts[$status])) {
      $statusCounts[$status]++;
    }

    if (!empty($car['last_maintenance'])) {
      $lastMaintenance = strtotime($car['last_maintenance']);
      if ($lastMaintenance && (time() - $lastMaintenance) > (90 * 86400)) {
        $needsService++;
      }
    }
  }
}
?>

<?php if ($isAdmin): ?>
  <div class="breadcrumb">Operations / Fleet</div>
  <div class="mb-3" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
    <div>
      <h1 class="mb-1">Fleet command centre</h1>
      <p class="muted mb-0">Monitor availability, maintenance, and assignments across every branch.</p>
    </div>
    <a href="index.php?url=fleet/create" class="btn primary">+ Add vehicle</a>
  </div>

  <?php if ($totalVehicles === 0): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No vehicles recorded</h3>
      <p class="muted mb-0">Add your first vehicle to begin assigning instructors and tracking maintenance reminders.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <div class="card stat-card">
        <h4 class="mb-1">Total vehicles</h4>
        <div class="value"><?= $totalVehicles; ?></div>
        <p class="muted mb-0">Across all active branches</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Available today</h4>
        <div class="value" style="color:#0b6b33;"><?= $statusCounts['available']; ?></div>
        <p class="muted mb-0">Ready to assign for upcoming classes</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">In session</h4>
        <div class="value" style="color:#2563eb;"><?= $statusCounts['assigned']; ?></div>
        <p class="muted mb-0">Actively scheduled with instructors</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Service follow-ups</h4>
        <div class="value" style="color:#b54708;"><?= $needsService; ?></div>
        <p class="muted mb-0">Over 90 days since last maintenance</p>
      </div>
    </div>

    <div class="card page-toolbar mb-2">
      <div class="search-group">
        <label for="fleetSearch">Quick search</label>
        <input id="fleetSearch" type="search" placeholder="Search by make, model, branch, or reg" autocomplete="off">
      </div>
      <div class="page-toolbar__meta">
        <div class="chip-group" role="group" aria-label="Status filters">
          <button type="button" class="chip active" data-status-filter="all"><span class="dot"></span>All</button>
          <button type="button" class="chip" data-status-filter="available"><span class="dot"></span>Available</button>
          <button type="button" class="chip" data-status-filter="assigned"><span class="dot"></span>Assigned</button>
          <button type="button" class="chip" data-status-filter="maintenance"><span class="dot"></span>Maintenance</button>
        </div>
        <small id="fleetResultCount">Showing <?= $totalVehicles; ?> vehicle<?= $totalVehicles === 1 ? '' : 's'; ?></small>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Vehicle</th>
            <th>Branch</th>
            <th>Status</th>
            <th>Last maintenance</th>
            <th class="right">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($fleet as $car): ?>
          <?php
            $status = strtolower($car['status'] ?? 'available');
            $searchable = strtolower(trim(implode(' ', array_filter([
              $car['make'] ?? '',
              $car['model'] ?? '',
              $car['registration_number'] ?? '',
              $car['branch_name'] ?? '',
            ]))));

            $badgeClass = 'badge gray';
            if ($status === 'available') {
              $badgeClass = 'badge green';
            } elseif ($status === 'assigned') {
              $badgeClass = 'badge yellow';
            } elseif ($status === 'maintenance') {
              $badgeClass = 'badge red';
            }

            $maintenanceLabel = '—';
            if (!empty($car['last_maintenance'])) {
              $timestamp = strtotime($car['last_maintenance']);
              if ($timestamp) {
                $maintenanceLabel = date('d M Y', $timestamp);
              }
            }
          ?>
          <tr data-fleet-row
              data-status="<?= htmlspecialchars($status); ?>"
              data-search="<?= htmlspecialchars($searchable); ?>">
            <td>
              <div class="stack">
                <div class="stacked">
                  <strong><?= htmlspecialchars(trim(($car['make'] ?? '') . ' ' . ($car['model'] ?? '')) ?: 'Vehicle'); ?></strong>
                  <span class="muted text-sm">Reg <?= htmlspecialchars($car['registration_number'] ?? '—'); ?></span>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($car['branch_name'] ?? 'Unassigned'); ?></td>
            <td><span class="<?= $badgeClass; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($status); ?></span></td>
            <td><?= htmlspecialchars($maintenanceLabel); ?></td>
            <td class="right actions">
              <a class="btn small outline" href="index.php?url=fleet/edit/<?= (int)$car['id']; ?>">Edit</a>
              <a class="btn small danger" href="index.php?url=fleet/destroy/<?= (int)$car['id']; ?>" onclick="return confirm('Remove this vehicle?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
          <tr data-empty-row style="display:none;">
            <td colspan="5" class="center muted">No vehicles match your filters.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <script>
      (function(){
        const searchInput = document.getElementById('fleetSearch');
        const chips = Array.from(document.querySelectorAll('[data-status-filter]'));
        const rows = Array.from(document.querySelectorAll('[data-fleet-row]'));
        const emptyRow = document.querySelector('[data-empty-row]');
        const countLabel = document.getElementById('fleetResultCount');
        const total = rows.length;
        let statusFilter = 'all';

        if (!rows.length) {
          return;
        }

        const updateCount = (visible) => {
          if (!countLabel) return;
          const plural = visible === 1 ? '' : 's';
          if (visible === total) {
            countLabel.textContent = `Showing ${visible} vehicle${plural}`;
          } else {
            countLabel.textContent = `Showing ${visible} of ${total} vehicles`;
          }
        };

        const applyFilters = () => {
          const query = (searchInput?.value || '').trim().toLowerCase();
          let visible = 0;

          rows.forEach((row) => {
            const status = row.dataset.status || 'available';
            const searchable = row.dataset.search || '';

            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            const matchesSearch = !query || searchable.includes(query);

            if (matchesStatus && matchesSearch) {
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
            statusFilter = chip.dataset.statusFilter || 'all';
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
    <h1 class="mb-1">Fleet availability</h1>
    <p class="muted mb-0">Plan lessons with confidence by checking vehicle status, maintenance, and branch assignments in real time.</p>
  </div>

  <?php if (empty($fleet)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No vehicles linked yet</h3>
      <p class="muted mb-0">Reach out to your admin team so they can allocate training cars to your branch.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <div class="card stat-card">
        <h4 class="mb-1">Ready to book</h4>
        <div class="value" style="color:#0b6b33;"><?= $statusCounts['available']; ?></div>
        <p class="muted mb-0">Coordinate with scheduling before confirming students</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Currently assigned</h4>
        <div class="value" style="color:#2563eb;"><?= $statusCounts['assigned']; ?></div>
        <p class="muted mb-0">Vehicles in active sessions</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Due for service</h4>
        <div class="value" style="color:#b54708;"><?= $needsService; ?></div>
        <p class="muted mb-0">Flag the branch manager for follow-up</p>
      </div>
    </div>

    <div class="card role-callout mb-2">
      <h2 class="mb-1">Teaching best practices</h2>
      <ul class="muted">
        <li>Attach lesson notes and vehicle condition photos after each drive.</li>
        <li>Use the shared calendar to avoid double-booking popular time slots.</li>
        <li>Trigger SMS reminders to students for early-morning sessions.</li>
      </ul>
    </div>

    <div class="cards instructor-fleet">
      <?php foreach ($fleet as $car): ?>
        <?php
          $status = strtolower($car['status'] ?? 'available');
          $badgeClass = 'badge gray';
          if ($status === 'available') {
            $badgeClass = 'badge green';
          } elseif ($status === 'assigned') {
            $badgeClass = 'badge yellow';
          } elseif ($status === 'maintenance') {
            $badgeClass = 'badge red';
          }

          $maintenanceLabel = 'Update log after inspection';
          if (!empty($car['last_maintenance'])) {
            $timestamp = strtotime($car['last_maintenance']);
            if ($timestamp) {
              $maintenanceLabel = 'Last serviced ' . date('d M Y', $timestamp);
            }
          }
        ?>
        <div class="card">
          <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:12px;">
            <div>
              <h3 class="mb-1"><?= htmlspecialchars(trim(($car['make'] ?? '') . ' ' . ($car['model'] ?? '')) ?: 'Vehicle'); ?></h3>
              <p class="muted mb-1">Reg <?= htmlspecialchars($car['registration_number'] ?? '—'); ?></p>
              <p class="muted mb-1">Branch: <?= htmlspecialchars($car['branch_name'] ?? 'Unassigned'); ?></p>
            </div>
            <span class="<?= $badgeClass; ?>" style="text-transform:capitalize;"><?= htmlspecialchars($status); ?></span>
          </div>
          <p class="mb-2 muted"><?= htmlspecialchars($maintenanceLabel); ?></p>
          <div class="stack" style="flex-wrap:wrap; gap:8px;">
            <a class="btn small outline" href="index.php?url=schedule/index">Reserve on calendar</a>
            <a class="btn small outline" href="index.php?url=student/index">Message students</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php else: ?>
  <div class="mb-3">
    <h1 class="mb-1">Meet our training fleet</h1>
    <p class="muted mb-0">Modern dual-control vehicles, meticulously maintained to give you a safe learning environment.</p>
  </div>

  <?php if (empty($fleet)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">Fleet update in progress</h3>
      <p class="muted mb-0">Our vehicles are being prepared. Check back soon to explore the fleet assigned to your branch.</p>
    </div>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($fleet as $car): ?>
        <div class="card">
          <h3 class="mb-1"><?= htmlspecialchars(trim(($car['make'] ?? '') . ' ' . ($car['model'] ?? '')) ?: 'Vehicle'); ?></h3>
          <p class="muted mb-1">Registration <?= htmlspecialchars($car['registration_number'] ?? '—'); ?></p>
          <p class="muted mb-1">Based at <?= htmlspecialchars($car['branch_name'] ?? 'our central garage'); ?></p>
          <ul class="muted mb-2">
            <li>Flexible scheduling with instant SMS confirmations</li>
            <li>Regular maintenance logs and safety inspections</li>
            <li>Compatible with all Origin course packages</li>
          </ul>
          <span class="status-pill">Status: <?= htmlspecialchars(ucfirst($car['status'] ?? 'available')); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
