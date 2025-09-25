<?php
class Instructor extends Model {
    public function all(): array {
        return $this->many("SELECT * FROM instructors");
    }

    public function getById(int $id): ?array {
        return $this->one("SELECT * FROM instructors WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        $this->run(
            "INSERT INTO instructors (user_id, availability) VALUES (?,?)",
            [$data['user_id'], json_encode($data['availability'] ?? [])]
        );
        return (int)$this->db->lastInsertId();
    }
}
