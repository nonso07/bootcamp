<?php
class RegistrationModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO registrations (student_id, course_id, bootcamp_id, tshirt_size, session, amount, payment_status, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['student_id'],
            $data['course_id'],
            $data['bootcamp_id'] ?? null,
            $data['tshirt_size'],
            $data['session'],
            $data['amount'],
            $data['payment_status'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
