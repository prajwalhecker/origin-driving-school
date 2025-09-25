<?php
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
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
            $this->db->rollBack();
        }
    }

    /** Create a student helper (returns user_id) */
    private function createStudent(): int
    {
        $email = 'stud.inv.'.uniqid().'@example.com';
        $hash  = password_hash('TestPass123', PASSWORD_DEFAULT);
        $this->db->prepare("
            INSERT INTO users (branch_id, created_at, email, first_name, last_name, password, phone, role, updated_at)
            VALUES (NULL, NOW(), ?, 'Inv', 'Student', ?, NULL, 'student', NOW())
        ")->execute([$email, $hash]);
        return (int)$this->db->lastInsertId();
    }

    public function testInvoiceCreateAndFetch(): void
    {
        $studentId = $this->createStudent();

        // create invoice
        $this->db->prepare("
          INSERT INTO invoices (student_id, amount, issued_date, due_date, status, created_at)
          VALUES (?, 199.99, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'unpaid', NOW())
        ")->execute([$studentId]);

        $invId = (int)$this->db->lastInsertId();
        $this->assertGreaterThan(0, $invId);

        // fetch it back
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id=?");
        $stmt->execute([$invId]);
        $invoice = $stmt->fetch();

        $this->assertNotEmpty($invoice);
        $this->assertSame('unpaid', $invoice['status']);
        $this->assertEquals(199.99, (float)$invoice['amount']);
    }

    public function testMarkInvoicePaidWhenFullyCovered(): void
    {
        $studentId = $this->createStudent();

        // create invoice for 150
        $this->db->prepare("
          INSERT INTO invoices (student_id, amount, issued_date, due_date, status, created_at)
          VALUES (?, 150.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'unpaid', NOW())
        ")->execute([$studentId]);
        $invId = (int)$this->db->lastInsertId();

        // pay 100 then 50
        $this->db->prepare("INSERT INTO payments (invoice_id, amount, method) VALUES (?, 100.00, 'manual')")->execute([$invId]);
        $this->db->prepare("INSERT INTO payments (invoice_id, amount, method) VALUES (?, 50.00, 'manual')")->execute([$invId]);

        // recompute remaining and mark paid (emulates controller logic)
        $stmt = $this->db->prepare("
            SELECT amount - COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id=?),0) AS remain
            FROM invoices WHERE id=?
        ");
        $stmt->execute([$invId,$invId]);
        $remain = (float)$stmt->fetchColumn();

        if ($remain <= 0) {
            $this->db->prepare("UPDATE invoices SET status='paid', updated_at=NOW() WHERE id=?")->execute([$invId]);
        }

        $status = $this->db->prepare("SELECT status FROM invoices WHERE id=?");
        $status->execute([$invId]);
        $this->assertSame('paid', $status->fetchColumn());
    }
}
