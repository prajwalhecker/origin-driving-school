<?php
class Branch extends Model {
    public function all(): array {
        return $this->many("SELECT * FROM branches");
    }

    public function getById(int $id): ?array {
        return $this->one("SELECT * FROM branches WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        $this->run(
            "INSERT INTO branches (name, address, phone) VALUES (?,?,?)",
            [$data['name'], $data['address'], $data['phone']]
        );
        return (int)$this->db->lastInsertId();
    }
}
