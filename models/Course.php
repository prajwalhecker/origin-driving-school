<?php
class Course extends Model {
    public function all(): array {
        return $this->many("SELECT * FROM courses");
    }

    public function getById(int $id): ?array {
        return $this->one("SELECT * FROM courses WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        $this->run(
            "INSERT INTO courses (title, description, fee) VALUES (?,?,?)",
            [$data['title'], $data['description'], $data['fee']]
        );
        return (int)$this->db->lastInsertId();
    }
}
