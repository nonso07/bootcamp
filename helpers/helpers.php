<?php
/**
 * Helper utilities for validation, sanitization, CSRF, uploads, and JSON responses.
 */
function sanitize(mixed $value): string
{
    if (is_array($value)) {
        return '';
    }

    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function validateRequired(array $data, array $fields): array
{
    $errors = [];

    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            $errors[] = $field;
        }
    }

    return $errors;
}

function jsonResponse(bool $success, string $message, array $payload = []): array
{
    return [
        'success' => $success,
        'message' => $message,
        'data' => $payload,
    ];
}

function generateStudentRegistrationNumber(PDO $pdo): string
{
    $year = date('Y');
    $stmt = $pdo->query("SELECT MAX(id) AS max_id FROM students");
    $maxId = (int) $stmt->fetchColumn();
    $nextId = $maxId + 1;
    return sprintf('HBT-STU-%s-%06d', $year, $nextId);
}

function generateInvoiceNumber(PDO $pdo): string
{
    $year = date('Y');
    $stmt = $pdo->query("SELECT MAX(id) AS max_id FROM invoices");
    $maxId = (int) $stmt->fetchColumn();
    $nextId = $maxId + 1;
    return sprintf('INV-%s-%06d', $year, $nextId);
}

function uploadStudentPhoto(array $file, string $uploadDir): ?string
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Image too large. Maximum 5MB allowed.');
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        throw new InvalidArgumentException('Invalid image type.');
    }

    $filename = uniqid('student_', true) . '.' . $ext;
    $target = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Failed to upload photo.');
    }

    return $filename;
}

function createActivityLog(PDO $pdo, string $message, int $adminId = 0): void
{
    $stmt = $pdo->prepare('INSERT INTO activity_logs (admin_id, activity, created_at) VALUES (?, ?, NOW())');
    $stmt->execute([$adminId, $message]);
}

function logError(string $message): void
{
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/registration_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

function ensureCsrfToken(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
