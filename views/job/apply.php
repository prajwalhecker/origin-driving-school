<h2>Instructor Job Application</h2>
<?php if(!empty($flash_success)) echo "<p style='color:green'>$flash_success</p>"; ?>
<form method="POST" enctype="multipart/form-data">
  <label>Name:</label><input type="text" name="name" required><br>
  <label>Email:</label><input type="email" name="email" required><br>
  <label>Phone:</label><input type="text" name="phone"><br>
  <label>License No:</label><input type="text" name="license_no"><br>
  <label>Experience (years):</label><input type="number" name="experience_years"><br>
  <label>Resume:</label><input type="file" name="resume"><br>
  <button type="submit">Apply</button>
</form>
