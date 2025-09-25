<?php
@session_start();

$courses = $courses ?? [];
$role = $_SESSION['role'] ?? 'guest';
$isAdmin = $role === 'admin';
$isInstructor = $role === 'instructor';

if ($isAdmin || $isInstructor) {
  $totalCourses = count($courses);
  $totalClasses = 0;
  $prices = [];

  foreach ($courses as $course) {
    if (!empty($course['class_count'])) {
      $totalClasses += (int)$course['class_count'];
    }
    if (!empty($course['price'])) {
      $prices[] = (float)$course['price'];
    }
  }

  $avgPrice = null;
  if (!empty($prices)) {
    $avgPrice = array_sum($prices) / count($prices);
  }
}
?>

<?php if ($isAdmin): ?>
  <div class="breadcrumb">Operations / Courses</div>
  <div class="mb-3" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
    <div>
      <h1 class="mb-1">Course catalogue</h1>
      <p class="muted mb-0">Set pricing, monitor class volume, and coordinate instructors for every program.</p>
    </div>
    <a href="index.php?url=course/create" class="btn primary">+ Add course</a>
  </div>

  <?php if ($totalCourses === 0): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No courses yet</h3>
      <p class="muted mb-0">Create your first course to begin enrolling students and generating invoices automatically.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <div class="card stat-card">
        <h4 class="mb-1">Active courses</h4>
        <div class="value"><?= $totalCourses; ?></div>
        <p class="muted mb-0">Available for student enrollment</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Total classes</h4>
        <div class="value" style="color:#0b6b33;"><?= $totalClasses; ?></div>
        <p class="muted mb-0">Across every published course</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Average price</h4>
        <div class="value" style="color:#2563eb;">
          <?= $avgPrice !== null ? '$' . number_format($avgPrice, 2) : '—'; ?>
        </div>
        <p class="muted mb-0">Helps balance revenue targets</p>
      </div>
    </div>

    <div class="card page-toolbar mb-2">
      <div class="search-group">
        <label for="courseSearch">Quick search</label>
        <input id="courseSearch" type="search" placeholder="Search by name, price, or description" autocomplete="off">
      </div>
      <div class="page-toolbar__meta">
        <div class="chip-group" role="group" aria-label="Course length filters">
          <button type="button" class="chip active" data-length-filter="all"><span class="dot"></span>All</button>
          <button type="button" class="chip" data-length-filter="short"><span class="dot"></span>Under 10 classes</button>
          <button type="button" class="chip" data-length-filter="standard"><span class="dot"></span>10-20 classes</button>
          <button type="button" class="chip" data-length-filter="extended"><span class="dot"></span>20+ classes</button>
        </div>
        <small id="courseResultCount">Showing <?= $totalCourses; ?> course<?= $totalCourses === 1 ? '' : 's'; ?></small>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Course</th>
            <th>Price</th>
            <th>Classes</th>
            <th>Created</th>
            <th class="right">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $course): ?>
          <?php
            $classCount = (int)($course['class_count'] ?? 0);
            $lengthGroup = 'all';
            if ($classCount > 0 && $classCount < 10) {
              $lengthGroup = 'short';
            } elseif ($classCount >= 10 && $classCount <= 20) {
              $lengthGroup = 'standard';
            } elseif ($classCount > 20) {
              $lengthGroup = 'extended';
            }

            $searchable = strtolower(trim(implode(' ', array_filter([
              $course['name'] ?? '',
              $course['description'] ?? '',
              (string)($course['price'] ?? ''),
            ]))));

            $createdLabel = '—';
            if (!empty($course['created_at'])) {
              $timestamp = strtotime($course['created_at']);
              if ($timestamp) {
                $createdLabel = date('d M Y', $timestamp);
              }
            }

            $description = $course['description'] ?? '';
            $preview = '—';
            if ($description !== '') {
              if (function_exists('mb_strimwidth')) {
                $preview = mb_strimwidth($description, 0, 72, '…', 'UTF-8');
              } else {
                $preview = strlen($description) > 72 ? substr($description, 0, 69) . '…' : $description;
              }
            }
          ?>
          <tr data-course-row
              data-length="<?= htmlspecialchars($lengthGroup); ?>"
              data-search="<?= htmlspecialchars($searchable); ?>">
            <td>
              <div class="stacked">
                <strong><?= htmlspecialchars($course['name'] ?? 'Course'); ?></strong>
                <span class="muted text-sm"><?= htmlspecialchars($preview); ?></span>
              </div>
            </td>
            <td>$<?= number_format((float)($course['price'] ?? 0), 2); ?></td>
            <td><?= $classCount ?: '—'; ?></td>
            <td><?= htmlspecialchars($createdLabel); ?></td>
            <td class="right actions">
              <a class="btn small outline" href="index.php?url=course/edit/<?= (int)$course['id']; ?>">Edit</a>
              <a class="btn small danger" href="index.php?url=course/destroy/<?= (int)$course['id']; ?>" onclick="return confirm('Delete this course?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
          <tr data-empty-row style="display:none;">
            <td colspan="5" class="center muted">No courses match your filters.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <script>
      (function(){
        const searchInput = document.getElementById('courseSearch');
        const chips = Array.from(document.querySelectorAll('[data-length-filter]'));
        const rows = Array.from(document.querySelectorAll('[data-course-row]'));
        const emptyRow = document.querySelector('[data-empty-row]');
        const countLabel = document.getElementById('courseResultCount');
        const total = rows.length;
        let lengthFilter = 'all';

        if (!rows.length) return;

        const updateCount = (visible) => {
          if (!countLabel) return;
          const plural = visible === 1 ? '' : 's';
          if (visible === total) {
            countLabel.textContent = `Showing ${visible} course${plural}`;
          } else {
            countLabel.textContent = `Showing ${visible} of ${total} courses`;
          }
        };

        const applyFilters = () => {
          const query = (searchInput?.value || '').trim().toLowerCase();
          let visible = 0;

          rows.forEach((row) => {
            const length = row.dataset.length || 'all';
            const searchable = row.dataset.search || '';

            const matchesLength = lengthFilter === 'all' || length === lengthFilter;
            const matchesSearch = !query || searchable.includes(query);

            if (matchesLength && matchesSearch) {
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
            lengthFilter = chip.dataset.lengthFilter || 'all';
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
    <h1 class="mb-1">Teaching programmes</h1>
    <p class="muted mb-0">Review the lesson structure, class counts, and pricing for each course you support.</p>
  </div>

  <?php if (empty($courses)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No courses assigned</h3>
      <p class="muted mb-0">Reach out to your admin team to be mapped to the correct curriculum.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <div class="card stat-card">
        <h4 class="mb-1">Courses you teach</h4>
        <div class="value"><?= $totalCourses; ?></div>
        <p class="muted mb-0">Sync lesson plans & reminders</p>
      </div>
      <div class="card stat-card">
        <h4 class="mb-1">Total sessions</h4>
        <div class="value" style="color:#0b6b33;"><?= $totalClasses; ?></div>
        <p class="muted mb-0">Stay ahead on notes & attachments</p>
      </div>
    </div>

    <div class="cards instructor-courses">
      <?php foreach ($courses as $course): ?>
        <div class="card">
          <h3 class="mb-1"><?= htmlspecialchars($course['name'] ?? 'Course'); ?></h3>
          <p class="muted mb-1"><?= htmlspecialchars($course['class_count'] ?? '—'); ?> classes • <?= '$' . number_format((float)($course['price'] ?? 0), 2); ?></p>
          <p class="muted mb-2"><?= htmlspecialchars($course['description'] ?? 'Curriculum details coming soon.'); ?></p>
          <div class="stack" style="flex-wrap:wrap; gap:8px;">
            <a class="btn small outline" href="index.php?url=schedule/index">View calendar</a>
            <a class="btn small outline" href="index.php?url=student/index">Student progress</a>
            <a class="btn small outline" href="index.php?url=invoice/index">Invoice history</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php else: ?>
  <div class="mb-3">
    <h1 class="mb-1">Origin Driving School courses</h1>
    <p class="muted mb-0">Choose a package that fits your schedule. Each enrolment unlocks reminders, digital notes, and access to our full training fleet.</p>
  </div>

  <?php if (empty($courses)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">New programmes launching soon</h3>
      <p class="muted mb-0">We’re updating our curriculum. Subscribe for alerts and SMS reminders once enrolment opens.</p>
    </div>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($courses as $course): ?>
        <div class="card">
          <img src="assets/images/courses/<?= (int)$course['id']; ?>.jpg" alt="<?= htmlspecialchars($course['name'] ?? 'Course'); ?>" class="mb-2 round" style="height:160px; width:100%; object-fit:cover;">
          <h3 class="mb-1"><?= htmlspecialchars($course['name'] ?? 'Course'); ?></h3>
          <p class="muted mb-1"><?= htmlspecialchars($course['description'] ?? 'Course details coming soon.'); ?></p>
          <p class="mb-2"><strong><?= '$' . number_format((float)($course['price'] ?? 0), 2); ?></strong> · <?= htmlspecialchars($course['class_count'] ?? 'Flexible'); ?> classes</p>
          <ul class="muted mb-2">
            <li>Scheduling assistance with SMS notifications</li>
            <li>Instructor feedback, notes, and attachments in your portal</li>
            <li>Easy payment tracking with automated invoices</li>
          </ul>
          <a href="index.php?url=auth/register" class="btn success">Enroll now</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
