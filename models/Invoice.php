<?php
class Invoice extends Model {
    public function all(): array {
        return $this->many("SELECT * FROM invoices");
    }

    public function getById(int $id): ?array {
        return $this->one("SELECT * FROM invoices WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        $this->run(
            "INSERT INTO invoices (student_id, amount, issued_date, due_date, status) 
             VALUES (?,?,?,?,?)",
            [$data['student_id'], $data['amount'], $data['issued_date'], $data['due_date'], $data['status']]
        );
        return (int)$this->db->lastInsertId();
    }
}
