<h1>Our Fleet</h1>

<div class="grid">
  <?php foreach ($fleet as $car): ?>
    <div class="card">
      <h3><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?></h3>
      <p><strong>Reg:</strong> <?= htmlspecialchars($car['registration_number']) ?></p>
      <p><strong>Status:</strong> <?= htmlspecialchars($car['status']) ?></p>
      <p><strong>Branch:</strong> <?= htmlspecialchars($car['branch_name'] ?? 'N/A') ?></p>
      <p><strong>Last Maintenance:</strong> <?= htmlspecialchars($car['last_maintenance']) ?></p>
    </div>
  <?php endforeach; ?>
</div>
