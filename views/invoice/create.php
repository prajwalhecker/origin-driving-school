<?php

$students = $students ?? [];
$courses  = $courses ?? [];
$defaultDue = date('Y-m-d', strtotime('+14 days'));

?>

<div class="breadcrumb">Finance / Invoices / New</div>
<h1 class="mb-2">Issue an invoice</h1>
<p class="muted mb-3">Send a new invoice to a student and optionally link it to a course for reporting.</p>

<form method="POST" action="index.php?url=invoice/store" class="form">
  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
  <div class="row">
    <div class="field">
      <label for="student_id">Student<span style="color:#dc3545;">*</span></label>
      <select id="student_id" name="student_id" required>
        <option value="">Select a student…</option>
        <?php foreach ($students as $student): ?>
          <option value="<?= (int)$student['id']; ?>">
            <?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?>
            <?= $student['email'] ? ' · ' . htmlspecialchars($student['email']) : ''; ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="help">Only active student accounts are listed.</p>
    </div>

    <div class="field">
      <label for="course_id">Course (optional)</label>
      <select id="course_id" name="course_id">
        <option value="">No course link</option>
        <?php foreach ($courses as $course): ?>
          <option value="<?= (int)$course['id']; ?>">
            <?= htmlspecialchars($course['name'] ?? ''); ?>
            <?php if (!empty($course['price'])): ?>
              (<?= '$' . number_format((float)$course['price'], 2); ?>)
            <?php endif; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row">
    <div class="field">
      <label for="amount">Invoice amount<span style="color:#dc3545;">*</span></label>
      <input id="amount" name="amount" type="number" step="0.01" min="0" placeholder="0.00" required>
    </div>
    <div class="field">
      <label for="due_date">Due date<span style="color:#dc3545;">*</span></label>
      <input id="due_date" name="due_date" type="date" value="<?= htmlspecialchars($defaultDue); ?>" required>
      <p class="help">Defaults to two weeks from today.</p>
    </div>
    <div class="field">
      <label for="status">Initial status</label>
      <select id="status" name="status">
        <option value="pending">Pending</option>
        <option value="partial">Partially paid</option>
        <option value="paid">Already paid</option>
      </select>
    </div>
  </div>

  <div class="actions">
    <button class="btn primary" type="submit">Create invoice</button>
    <a class="btn outline" href="index.php?url=invoice/index">Cancel</a>
  </div>
</form>
