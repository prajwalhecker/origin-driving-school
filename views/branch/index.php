<h1>Our Branches</h1>
<div class="grid">
  <?php foreach ($branches as $b): ?>
    <div class="card">
      <img src="assets/images/branches/<?=$b['id']?>.jpg" alt="<?=htmlspecialchars($b['name'])?>">
      <h2><?=htmlspecialchars($b['name'])?></h2>
      <p><?=htmlspecialchars($b['address'])?></p>
      <a href="index.php?url=branch/bookTour/<?=$b['id']?>" class="btn success">Book a Tour</a>
    </div>
  <?php endforeach; ?>
</div>
