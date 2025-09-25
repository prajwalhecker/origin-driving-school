<?php
require_once __DIR__ . "/../core/Model.php";

class User extends Model {
    protected $table = "users";

   public function findByEmailOrPhone($input) {
    $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = :input OR phone = :input");
    $stmt->execute(['input' => $input]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function verifyPassword($input, $password) {
    $user = $this->findByEmailOrPhone($input);
    if ($user && hash('sha256', $password) === $user['password']) {
        return $user;
    }
    return false;
}


    public function createUser($data) {
        // hash password before insert
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->create($data);
    }

    public function getInstructors() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} WHERE role = 'instructor'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudents() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} WHERE role = 'student'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
