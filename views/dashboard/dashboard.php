<?php
if (!defined('BASE_PATH')) { require_once __DIR__ . '/../../config/config.php'; }
$userRole = 'Guest';
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $userRole = $userRole ?? 'User';
}
?>
<div class="dashboard-container">
    <h2>Welcome back, <?= $_SESSION['user_name'] ?? 'User' ?></h2>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <p>Here’s your admin overview:</p>
        <div class="dashboard-cards">
            <div class="card">
                <h3><?= $data['stats']['total_students'] ?></h3>
                <p>Students</p>
            </div>
            <div class="card">
                <h3><?= $data['stats']['total_instructors'] ?></h3>
                <p>Instructors</p>
            </div>
            <div class="card">
                <h3><?= $data['stats']['total_bookings'] ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="card">
                <h3><?= $data['stats']['active_branches'] ?></h3>
                <p>Branches</p>
            </div>
        </div>

        <h3>Upcoming Bookings</h3>
        <ul class="dashboard-list">
            <?php foreach ($data['upcoming_bookings'] as $booking): ?>
                <li>
                    <?= $booking['date'] ?> @ <?= $booking['start_time'] ?> —
                    <?= $booking['student_name'] ?> with <?= $booking['instructor_name'] ?>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php elseif ($_SESSION['role'] === 'student'): ?>
        <p>You have <?= $data['total_bookings'] ?> total bookings.</p>
        <h3>Upcoming Lessons</h3>
        <ul class="dashboard-list">
            <?php foreach ($data['upcoming_lessons'] as $lesson): ?>
                <li>
                    <?= $lesson['date'] ?> @ <?= $lesson['start_time'] ?> —
                    <?= $lesson['course_title'] ?> with <?= $lesson['instructor_name'] ?>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php elseif ($_SESSION['role'] === 'instructor'): ?>
        <p>You have <?= $data['upcoming_lessons'] ?> upcoming lessons.</p>
        <h3>Schedule</h3>
        <ul class="dashboard-list">
            <?php foreach ($data['schedule'] as $lesson): ?>
                <li>
                    <?= $lesson['date'] ?> @ <?= $lesson['start_time'] ?> —
                    <?= $lesson['student_name'] ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once LAYOUT_PATH . '/footer.php'; ?>