$branch_id = $_POST['branch_id'] ?? null;
$vehicle_type = $_POST['vehicle_type'] ?? null;
$course_id = $_POST['course_id'] ?? null;
$address = trim($_POST['address'] ?? '');
$preferred_days = $_POST['preferred_days'] ?? null;
$preferred_time = $_POST['preferred_time'] ?? null;
$start_date = $_POST['start_date'] ?? null;

// Fix: convert arrays to strings
if (is_array($course_id)) {
    $course_id = implode(',', $course_id);
}
if (is_array($preferred_days)) {
    $preferred_days = implode(',', $preferred_days);
}
if (is_array($preferred_time)) {
    $preferred_time = implode(',', $preferred_time);
}
