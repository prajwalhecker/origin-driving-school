<h1>My Profile</h1>

<div class="card">
  <p><strong>Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
  <p><strong>Phone:</strong> <?= htmlspecialchars($student['phone']) ?></p>
  <p><strong>Address:</strong> <?= htmlspecialchars($student['address'] ?? '-') ?></p>
  <p><strong>Vehicle Type:</strong> <?= htmlspecialchars($student['vehicle_type'] ?? '-') ?></p>
  <p><strong>Course:</strong> <?= htmlspecialchars($student['course'] ?? '-') ?></p>
</div>
