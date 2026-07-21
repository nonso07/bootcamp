<?php
class ParentModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO parents (fullname, relationship, occupation, phone, whatsapp, email, emergency_contact, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['name'],
            $data['relationship'],
            $data['occupation'],
            $data['phone'],
            $data['whatsapp'],
            $data['email'],
            $data['emergency_contact'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
