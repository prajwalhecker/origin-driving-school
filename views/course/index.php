<?php
@session_start();

$courses = $courses ?? [];
$role = $_SESSION['role'] ?? 'guest';
$isAdmin = $role === 'admin';
$isInstructor = $role === 'instructor';
$isStudent = $role === 'student';

$studentProfile = $studentProfile ?? [];
$studentPreferredDays = [];
if (!empty($studentProfile['preferred_days'])) {
  $studentPreferredDays = array_filter(array_map('trim', explode(',', $studentProfile['preferred_days'])));
}

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
  <div class="mb-3" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div>
      <h1 class="mb-1">Course Management</h1>
      <p class="muted mb-0">Create, edit, and monitor courses offered at your driving school.</p>
    </div>
    <a class="btn primary" href="index.php?url=course/create">+ Add Course</a>
  </div>

  <?php if (empty($courses)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No courses yet</h3>
      <p class="muted mb-0">Click “Add Course” to create your first programme.</p>
    </div>
  <?php else: ?>
    <div class="cards mb-3">
      <div class="card">
        <h4 class="mb-1">Total Courses</h4>
        <div style="font-size:1.6rem;font-weight:700;"><?= $totalCourses; ?></div>
        <p class="muted mb-0">Programmes available</p>
      </div>
      <div class="card">
        <h4 class="mb-1">Total Classes</h4>
        <div style="font-size:1.6rem;font-weight:700; color:#0b6b33;"><?= $totalClasses; ?></div>
        <p class="muted mb-0">Across all programmes</p>
      </div>
      <div class="card">
        <h4 class="mb-1">Average Price</h4>
        <div style="font-size:1.6rem;font-weight:700; color:#842029;">
          <?= $avgPrice ? '$' . number_format($avgPrice, 2) : '—'; ?>
        </div>
        <p class="muted mb-0">Per course</p>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Classes</th>
            <th class="right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $course): ?>
            <tr>
              <td><?= htmlspecialchars($course['name']); ?></td>
              <td><?= htmlspecialchars($course['description']); ?></td>
              <td>$<?= number_format((float)$course['price'], 2); ?></td>
              <td><?= (int)$course['class_count']; ?></td>
              <td class="right">
                <a class="btn small outline" href="index.php?url=course/edit/<?= (int)$course['id']; ?>">Edit</a>
                <a class="btn small danger" href="index.php?url=course/destroy/<?= (int)$course['id']; ?>" onclick="return confirm('Delete this course?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
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

<?php elseif ($isStudent): ?>
  <?php
    $currentCourseName = $studentProfile['course_name'] ?? null;
    $currentCoursePrice = $studentProfile['course_price'] ?? null;
    $currentClassCount = $studentProfile['class_count'] ?? null;
    $currentBranch = $studentProfile['branch_name'] ?? ($_SESSION['branch_name'] ?? null);
    $currentStart = $studentProfile['start_date'] ?? null;
    $currentTime = $studentProfile['preferred_time'] ?? '';
    $preferredDaysLabel = $studentPreferredDays ? implode(', ', $studentPreferredDays) : 'No preference set';
  ?>

  <div class="breadcrumb">Student / Courses</div>
  <div class="mb-3">
    <h1 class="mb-1">Your learning plan</h1>
    <p class="muted mb-0">Review the course you are enrolled in and explore other programs. Switching courses will automatically create a new invoice for you.</p>
  </div>

  <div class="cards mb-3">
    <div class="card">
      <h4 class="mb-1">Current course</h4>
      <?php if ($currentCourseName): ?>
        <p class="mb-0"><strong><?= htmlspecialchars($currentCourseName); ?></strong></p>
        <p class="muted mb-0">Fee: <?= '$' . number_format((float)$currentCoursePrice, 2); ?><?= $currentClassCount ? ' • ' . (int)$currentClassCount . ' classes' : ''; ?></p>
      <?php else: ?>
        <p class="mb-0">You are not enrolled in a course yet.</p>
      <?php endif; ?>
    </div>
    <div class="card">
      <h4 class="mb-1">Branch</h4>
      <p class="mb-0"><strong><?= $currentBranch ? htmlspecialchars($currentBranch) : 'Choose a branch'; ?></strong></p>
      <p class="muted mb-0">Lesson schedules and vehicles follow this location.</p>
    </div>
    <div class="card">
      <h4 class="mb-1">Lesson preferences</h4>
      <p class="mb-0">Start date: <?= $currentStart ? date('d M Y', strtotime($currentStart)) : 'Not set'; ?></p>
      <p class="mb-0">Preferred time: <?= $currentTime ? htmlspecialchars($currentTime) : 'No preference'; ?></p>
      <p class="muted mb-0">Days: <?= htmlspecialchars($preferredDaysLabel); ?></p>
    </div>
  </div>

  <div class="card mb-3">
    <div class="stack" style="justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
      <div>
        <h3 class="mb-1">Update your enrolment</h3>
        <p class="muted mb-0">Need to change your program or branch? Update your enrolment and we will handle the invoices automatically.</p>
      </div>
      <a class="btn success" href="index.php?url=student/enrollment">Manage enrolment</a>
    </div>
  </div>

  <?php if (empty($courses)): ?>
    <div class="card empty-state-card">
      <h3 class="mb-1">No courses published yet</h3>
      <p class="muted mb-0">Check back soon for new driving programs to join.</p>
    </div>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($courses as $course): ?>
        <?php $isCurrent = $currentCourseName && (int)$studentProfile['course_id'] === (int)$course['id']; ?>
        <div class="card">
          <h3 class="mb-1" style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
            <span><?= htmlspecialchars($course['name'] ?? 'Course'); ?></span>
            <?php if ($isCurrent): ?><span class="badge green">Enrolled</span><?php endif; ?>
          </h3>
          <p class="muted mb-1"><?= htmlspecialchars($course['description'] ?? 'Course details coming soon.'); ?></p>
          <p class="mb-2"><strong><?= '$' . number_format((float)($course['price'] ?? 0), 2); ?></strong> · <?= htmlspecialchars($course['class_count'] ?? 'Flexible'); ?> classes</p>
          <div class="stack" style="gap:8px; flex-wrap:wrap;">
            <a class="btn small outline" href="index.php?url=student/enrollment&amp;course=<?= (int)$course['id']; ?>">Select this course</a>
            <a class="btn small outline" href="index.php?url=invoice/index">View invoices</a>
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
