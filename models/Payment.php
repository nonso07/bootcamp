<?php
class PaymentModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO payments (registration_id, amount, currency, payment_method, payment_gateway, transaction_reference, cinetpay_transaction_id, status, gateway_response, paid_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['registration_id'],
            $data['amount'],
            $data['currency'] ?? 'NGN',
            $data['payment_method'],
            $data['payment_gateway'],
            $data['transaction_reference'] ?? null,
            $data['cinetpay_transaction_id'] ?? null,
            $data['status'],
            $data['gateway_response'] ?? null,
            $data['paid_at'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
