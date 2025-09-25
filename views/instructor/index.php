<?php
  @session_start();
  $currentRole = $_SESSION['role'] ?? null;
?>
<?php if ($currentRole === 'admin'): ?>
  <!-- Admin view -->
  <table border="1" cellpadding="8">
    <tr>
      <th>Photo</th>
      <th>Name</th>
      <th>Experience</th>
      <th>Address</th>
      <th>Phone</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($instructors as $inst): ?>
      <tr>
        <td><img src="uploads/instructors/<?= htmlspecialchars($inst['photo'] ?? 'default.png') ?>" width="80"></td>
        <td><?= htmlspecialchars($inst['name']) ?></td>
        <td><?= htmlspecialchars($inst['experience']) ?></td>
        <td><?= htmlspecialchars($inst['address']) ?></td>
        <td><?= htmlspecialchars($inst['phone']) ?></td>
        <td>
          <a href="index.php?url=instructor/edit/<?= $inst['id'] ?>">Edit</a>
          <a href="index.php?url=instructor/destroy/<?= $inst['id'] ?>" onclick="return confirm('Delete this instructor?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

<?php else: ?>
<h1>Meet Our Instructors</h1>
<div class="grid">
  <?php foreach ($instructors as $inst): ?>
    <div class="card">
      <img src="assets/images/<?= htmlspecialchars($inst['photo'] ?? 'default.png') ?>" 
           alt="<?= htmlspecialchars($inst['name']) ?>" width="200">
      <h3><?= htmlspecialchars($inst['name']) ?></h3>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
