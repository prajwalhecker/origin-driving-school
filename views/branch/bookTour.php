<h1>Book a Tour at <?=htmlspecialchars($branch['name'])?></h1>
<form method="POST" action="index.php?url=branch/bookTour/<?=$branch['id']?>">
    <div class="field">
        <label for="name">Your Name</label>
        <input type="text" name="name" required>
    </div>
    <div class="field">
        <label for="email">Your Email</label>
        <input type="email" name="email" required>
    </div>
    <div class="field">
        <label for="preferred_date">Preferred Date</label>
        <input type="date" name="preferred_date" required>
    </div>
    <button type="submit" class="btn">Book Tour</button>
</form>
