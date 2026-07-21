<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/RegistrationController.php';

$controller = new RegistrationController();
$response = $controller->handle();

if (!empty($response['success'])) {
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
} else {
    echo json_encode([
        'success' => false,
        'message' => $response['message'] ?? 'Registration failed.',
    ], JSON_UNESCAPED_SLASHES);
}
