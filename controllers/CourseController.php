<?php
class CourseController extends Controller {

    // Public courses listing
    public function index() {
        $stmt = $this->db->query("SELECT * FROM courses ORDER BY created_at DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view("course/index", ['courses' => $rows]);
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
        $stmt->execute([$name,$desc,$price]);

        $this->flash('flash_success','Course created.');
        $this->redirect("index.php?url=course/index");
    }

    public function edit($id) {
        $this->requireRole('admin');
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id=?");
        $stmt->execute([(int)$id]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$c) { return $this->redirect("index.php?url=course/index"); }

        $this->view("course/edit", ['course'=>$c]);
    }

    public function update($id) {
        $this->requireRole('admin');
        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);

        $stmt = $this->db->prepare(
            "UPDATE courses SET name=?, description=?, price=?, updated_at=NOW() WHERE id=?"
        );
        $stmt->execute([$name,$desc,$price,(int)$id]);

        $this->flash('flash_success','Course updated.');
        $this->redirect("index.php?url=course/index");
    }

    public function destroy($id) {
        $this->requireRole('admin');
        $this->db->prepare("DELETE FROM courses WHERE id=?")->execute([(int)$id]);
        $this->flash('flash_success','Course deleted.');
        $this->redirect("index.php?url=course/index");
    }
}
