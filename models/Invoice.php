<?php
class InvoiceModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO invoices (invoice_no, registration_id, invoice_date, due_date, subtotal, discount, total, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['invoice_no'],
            $data['registration_id'],
            $data['invoice_date'],
            $data['due_date'],
            $data['subtotal'],
            $data['discount'],
            $data['total'],
            $data['payment_status'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function addItem(int $invoiceId, float $amount): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$invoiceId, 'Course Fee', 1, $amount, $amount]);
    }
}
