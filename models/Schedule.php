<?php
class Schedule extends Model {
    public function all(): array {
        return $this->many("SELECT * FROM schedules ORDER BY start_datetime");
    }

    public function getById(int $id): ?array {
        return $this->one("SELECT * FROM schedules WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        $this->run(
            "INSERT INTO schedules (student_id, instructor_id, start_datetime, end_datetime, status) 
             VALUES (?,?,?,?,?)",
            [$data['student_id'], $data['instructor_id'], $data['start_datetime'], $data['end_datetime'], $data['status']]
        );
        return (int)$this->db->lastInsertId();
    }

    public function checkConflict($instructorId, $start, $end): bool {
        $row = $this->one(
            "SELECT COUNT(*) as cnt FROM schedules 
             WHERE instructor_id=? 
             AND status='scheduled'
             AND (start_datetime < ? AND end_datetime > ?)",
            [$instructorId, $end, $start]
        );
        return $row['cnt'] > 0;
    }
}
