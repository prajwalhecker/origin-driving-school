<?php
use PHPUnit\Framework\TestCase;

final class StudentTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = $GLOBALS['pdo'];
        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack(); // undo any changes
        }
    }

    public function testStudentUserCreationAndFetch(): void
    {
        // Arrange: create a student user
        $email = 'student.test.'.uniqid().'@example.com';
        $hash  = password_hash('TestPass123', PASSWORD_DEFAULT);

        $this->db->prepare("
            INSERT INTO users (branch_id, created_at, email, first_name, last_name, password, phone, role, updated_at)
            VALUES (NULL, NOW(), ?, 'Test', 'Student', ?, '0400000000', 'student', NOW())
        ")->execute([$email, $hash]);

        $id = (int)$this->db->lastInsertId();
        $this->assertGreaterThan(0, $id, "User insert should return an id");

        // Act: fetch from DB using the same pattern your model would
        $stmt = $this->db->prepare("SELECT id, email, role FROM users WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        // Assert
        $this->assertNotEmpty($row, "Inserted user should be retrievable");
        $this->assertSame($email, $row['email']);
        $this->assertSame('student', $row['role']);
    }

    public function testDuplicateEmailBlocked(): void
    {
        $email = 'dupe.'.uniqid().'@example.com';
        $hash  = password_hash('TestPass123', PASSWORD_DEFAULT);

        // first insert OK
        $this->db->prepare("
            INSERT INTO users (branch_id, created_at, email, first_name, last_name, password, phone, role, updated_at)
            VALUES (NULL, NOW(), ?, 'A', 'One', ?, NULL, 'student', NOW())
        ")->execute([$email, $hash]);

        // second insert same email should fail due to UNIQUE(email)
        $this->expectException(PDOException::class);
        $this->db->prepare("
            INSERT INTO users (branch_id, created_at, email, first_name, last_name, password, phone, role, updated_at)
            VALUES (NULL, NOW(), ?, 'B', 'Two', ?, NULL, 'student', NOW())
        ")->execute([$email, $hash]);
    }
}
