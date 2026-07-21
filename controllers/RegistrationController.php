<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../services/RegistrationService.php';

class RegistrationController
{
    public function handle(): array
    {
        session_start();
        ensureCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return jsonResponse(false, 'Invalid request method.');
        }

        $rawInput = file_get_contents('php://input');
        $data = [];

        if ($rawInput !== '') {
            $data = json_decode($rawInput, true) ?: [];
        } else {
            $data = $_POST;
        }

        if (!isset($data['csrf_token']) || !verifyCsrfToken($data['csrf_token'])) {
            return jsonResponse(false, 'Invalid CSRF token.');
        }

        $service = new RegistrationService(Database::getInstance());
        return $service->register($data);
    }
}
