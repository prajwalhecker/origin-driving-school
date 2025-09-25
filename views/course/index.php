<h1>Our Courses</h1>
<div class="grid">
  <?php foreach ($courses as $c): ?>
    <div class="card">
      <img src="assets/images/courses/<?=$c['id']?>.jpg" alt="<?=htmlspecialchars($c['name'])?>">
      <h2><?=htmlspecialchars($c['name'])?></h2>
      <p><?=htmlspecialchars($c['description'])?></p>
      <p><strong>$<?=number_format($c['price'], 2)?></strong> — <?=$c['class_count']?> classes</p>
      <a href="index.php?url=auth/register" class="btn">Enroll Now</a>
    </div>
  <?php endforeach; ?>
</div>
