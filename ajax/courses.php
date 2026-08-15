<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->query('SELECT course_id, course_name, description, credits, course_price FROM courses ORDER BY course_id');
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($courses, JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([], JSON_UNESCAPED_SLASHES);
}
