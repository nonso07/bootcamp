<?php
class StudentModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO students (registration_no, parent_id, first_name, last_name, gender, dob, school_id, class_level, nationality, address, photo, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['registration_no'],
            $data['parent_id'],
            $data['first_name'],
            $data['last_name'],
            $data['gender'],
            $data['dob'],
            $data['school_id'] ?? null,
            $data['class_level'],
            $data['nationality'],
            $data['address'],
            $data['photo'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
