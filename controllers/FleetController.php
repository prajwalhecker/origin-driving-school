<?php
class FleetController extends Controller {

    // Public fleet listing
    public function index() {
        $stmt = $this->db->query("
            SELECT v.*, b.name AS branch_name 
            FROM vehicles v
            LEFT JOIN branches b ON v.branch_id = b.id
            ORDER BY v.created_at DESC
        ");
        $fleet = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view("fleet/index", compact('fleet'));
    }

    // ---------------------------
    // Admin-only CRUD below
    // ---------------------------

    public function create() {
        $this->requireRole('admin');
        // fetch branches for dropdown
        $branches = $this->db->query("SELECT id, name FROM branches ORDER BY name ASC")
                             ->fetchAll(PDO::FETCH_ASSOC);
        $this->view("fleet/create", compact('branches'));
    }

    public function store() {
        $this->requireRole('admin');
        $branch = (int)($_POST['branch_id'] ?? 0);
        $reg    = trim($_POST['registration_number'] ?? '');
        $make   = trim($_POST['make'] ?? '');
        $model  = trim($_POST['model'] ?? '');
        $status = trim($_POST['status'] ?? 'available');
        $last   = $_POST['last_maintenance'] ?? date('Y-m-d');

        if (!$branch || !$reg || !$make || !$model) {
            $this->flash('flash_error','Branch, reg no, make, and model are required.');
            return $this->redirect("index.php?url=fleet/create");
        }

        $stmt = $this->db->prepare("
            INSERT INTO vehicles (branch_id, registration_number, make, model, status, last_maintenance, created_at)
            VALUES (?,?,?,?,?,?,NOW())
        ");
        $stmt->execute([$branch,$reg,$make,$model,$status,$last]);

        $this->flash('flash_success','Vehicle added.');
        $this->redirect("index.php?url=fleet/index");
    }

    public function edit($id) {
        $this->requireRole('admin');
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id=?");
        $stmt->execute([(int)$id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            return $this->redirect("index.php?url=fleet/index");
        }

        $branches = $this->db->query("SELECT id, name FROM branches ORDER BY name ASC")
                             ->fetchAll(PDO::FETCH_ASSOC);

        $this->view("fleet/edit", compact('car','branches'));
    }

    public function update($id) {
        $this->requireRole('admin');
        $branch = (int)($_POST['branch_id'] ?? 0);
        $reg    = trim($_POST['registration_number'] ?? '');
        $make   = trim($_POST['make'] ?? '');
        $model  = trim($_POST['model'] ?? '');
        $status = trim($_POST['status'] ?? 'available');
        $last   = $_POST['last_maintenance'] ?? date('Y-m-d');

        $stmt = $this->db->prepare("
            UPDATE vehicles 
            SET branch_id=?, registration_number=?, make=?, model=?, status=?, last_maintenance=?, updated_at=NOW()
            WHERE id=?
        ");
        $stmt->execute([$branch,$reg,$make,$model,$status,$last,(int)$id]);

        $this->flash('flash_success','Vehicle updated.');
        $this->redirect("index.php?url=fleet/index");
    }

    public function destroy($id) {
        $this->requireRole('admin');
        $this->db->prepare("DELETE FROM vehicles WHERE id=?")->execute([(int)$id]);
        $this->flash('flash_success','Vehicle removed.');
        $this->redirect("index.php?url=fleet/index");
    }
}
