<h2>Job Applications</h2>
<table border="1">
  <tr><th>Name</th><th>Email</th><th>Phone</th><th>License</th><th>Experience</th><th>Status</th><th>Resume</th></tr>
  <?php foreach($apps as $a): ?>
  <tr>
    <td><?= htmlspecialchars($a['name']) ?></td>
    <td><?= htmlspecialchars($a['email']) ?></td>
    <td><?= htmlspecialchars($a['phone']) ?></td>
    <td><?= htmlspecialchars($a['license_no']) ?></td>
    <td><?= $a['experience_years'] ?></td>
    <td><?= $a['status'] ?></td>
    <td><?php if($a['resume_path']) echo "<a href='".$a['resume_path']."' target='_blank'>View</a>"; ?></td>
  </tr>
  <?php endforeach; ?>
</table>
