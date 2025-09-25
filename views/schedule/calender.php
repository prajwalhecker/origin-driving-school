<h1>Class Schedule</h1>

<table class="table">
  <thead>
    <tr>
      <th>Date</th>
      <th>Time</th>
      <th>Student</th>
      <th>Instructor</th>
      <th>Course</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($schedules as $s): ?>
      <tr>
        <td><?= date("Y-m-d", strtotime($s['start_time'])) ?></td>
        <td><?= date("H:i", strtotime($s['start_time'])) ?> - <?= date("H:i", strtotime($s['end_time'])) ?></td>
        <td><?= htmlspecialchars($s['student_name']) ?></td>
        <td><?= htmlspecialchars($s['instructor_name']) ?></td>
        <td><?= htmlspecialchars($s['course_title']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
