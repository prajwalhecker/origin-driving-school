<?php
class Fleet extends Model {
    public function all(): array {
        return $this->many("SELECT * FROM fleet");
    }

    public function getById(int $id): ?array {
        return $this->one("SELECT * FROM fleet WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        $this->run(
            "INSERT INTO fleet (vehicle_type, model, plate_no, branch_id) VALUES (?,?,?,?)",
            [$data['vehicle_type'], $data['model'], $data['plate_no'], $data['branch_id']]
        );
        return (int)$this->db->lastInsertId();
    }
}
