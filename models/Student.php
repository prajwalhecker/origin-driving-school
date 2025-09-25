<?php
class Student extends Model {
    public function all(): array {
        return $this->many("SELECT * FROM students");
    }

    public function getById(int $id): ?array {
        return $this->one("SELECT * FROM students WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        $this->run(
            "INSERT INTO students (user_id, licence_type, dob) VALUES (?,?,?)",
            [$data['user_id'], $data['licence_type'], $data['dob']]
        );
        return (int)$this->db->lastInsertId();
    }
}
