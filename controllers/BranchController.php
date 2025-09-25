<?php
class BranchController extends Controller {

    // List branches (public page)
    public function index() {
        $stmt = $this->db->query("SELECT * FROM branches ORDER BY created_at DESC");
        $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view("branch/index", ['branches' => $branches]);
    }

    // Show form for booking a tour (public)
    public function bookTour($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $date  = $_POST['preferred_date'] ?? '';

            if (!$name || !$email || !$date) {
                $this->flash('flash_error', 'All fields are required.');
                return $this->redirect("index.php?url=branch/bookTour/$id");
            }

            $stmt = $this->db->prepare("INSERT INTO branch_tours (branch_id, name, email, preferred_date) VALUES (?,?,?,?)");
            $stmt->execute([(int)$id, $name, $email, $date]);

            $this->flash('flash_success', 'Tour booked successfully! We’ll contact you soon.');
            return $this->redirect("index.php?url=branch/index");
        }

        $stmt = $this->db->prepare("SELECT * FROM branches WHERE id=?");
        $stmt->execute([(int)$id]);
        $branch = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$branch) {
            http_response_code(404);
            echo "Branch not found.";
            return;
        }

        $this->view("branch/bookTour", ['branch' => $branch]);
    }

    // ---------------------------
    // Admin-only CRUD below
    // ---------------------------

    public function create() {
        $this->requireRole('admin');
        $this->view("branch/create");
    }

    public function store() {
        $this->requireRole('admin');
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$name || !$address) {
            $this->flash('flash_error', 'Name and address required.');
            return $this->view("branch/create");
        }

        $stmt = $this->db->prepare("INSERT INTO branches (name, address, phone, created_at) VALUES (?,?,?,NOW())");
        $stmt->execute([$name, $address, $phone]);

        $this->flash('flash_success', 'Branch added.');
        $this->redirect("index.php?url=branch/index");
    }

    public function edit($id) {
        $this->requireRole('admin');
        $stmt = $this->db->prepare("SELECT * FROM branches WHERE id=?");
        $stmt->execute([(int)$id]);
        $branch = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$branch) {
            return $this->redirect("index.php?url=branch/index");
        }

        $this->view("branch/edit", ['branch' => $branch]);
    }

    public function update($id) {
        $this->requireRole('admin');
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $stmt = $this->db->prepare("UPDATE branches SET name=?, address=?, phone=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$name, $address, $phone, (int)$id]);

        $this->flash('flash_success', 'Branch updated.');
        $this->redirect("index.php?url=branch/index");
    }

    public function destroy($id) {
        $this->requireRole('admin');
        $stmt = $this->db->prepare("DELETE FROM branches WHERE id=?");
        $stmt->execute([(int)$id]);

        $this->flash('flash_success', 'Branch deleted.');
        $this->redirect("index.php?url=branch/index");
    }
}
