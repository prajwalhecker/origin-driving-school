<?php
class CourseController extends Controller {

    // Public courses listing
    public function index() {
        @session_start();

        $stmt = $this->db->query("SELECT * FROM courses ORDER BY created_at DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $studentProfile = [];
        if (($_SESSION['role'] ?? '') === 'student' && !empty($_SESSION['user_id'])) {
            $profileStmt = $this->db->prepare("SELECT
                sp.course_id,
                sp.start_date,
                sp.preferred_time,
                sp.preferred_days,
                sp.vehicle_type,
                sp.branch_id,
                c.name AS course_name,
                c.price AS course_price,
                c.class_count,
                b.name AS branch_name
              FROM student_profiles sp
              LEFT JOIN courses c ON c.id = sp.course_id
              LEFT JOIN branches b ON b.id = sp.branch_id
              WHERE sp.user_id = ?");
            $profileStmt->execute([$_SESSION['user_id']]);
            $studentProfile = $profileStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        $this->view("course/index", [
            'courses'        => $rows,
            'studentProfile' => $studentProfile,
        ]);
    }

    // ---------------------------
    // Admin-only CRUD below
    // ---------------------------

    public function create() {
        $this->requireRole('admin');
        $this->view("course/create");
    }

    public function store() {
        $this->requireRole('admin');
        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);

        if (!$name || $price <= 0) {
            $this->flash('flash_error','Name and price required.');
            return $this->view("course/create");
        }

        $stmt = $this->db->prepare(
            "INSERT INTO courses (name, description, price, created_at) VALUES (?,?,?,NOW())"
        );
        $stmt->execute([$name, $desc, $price]);

        $this->flash('flash_success','Course created successfully.');
        $this->redirect('index.php?url=course/index');
    }

    public function edit($id) {
        $this->requireRole('admin');
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id=?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            $this->flash('flash_error','Course not found.');
            $this->redirect('index.php?url=course/index');
        }

        $this->view("course/edit", compact('course'));
    }

    public function update($id) {
        $this->requireRole('admin');
        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);

        if (!$name || $price <= 0) {
            $this->flash('flash_error','Name and price required.');
            return $this->view("course/edit", ['course' => ['id'=>$id,'name'=>$name,'description'=>$desc,'price'=>$price]]);
        }

        $stmt = $this->db->prepare("UPDATE courses SET name=?, description=?, price=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$name, $desc, $price, $id]);

        $this->flash('flash_success','Course updated successfully.');
        $this->redirect('index.php?url=course/index');
    }

    public function destroy($id) {
        $this->requireRole('admin');
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id=?");
        $stmt->execute([$id]);

        $this->flash('flash_success','Course deleted successfully.');
        $this->redirect('index.php?url=course/index');
    }
}
