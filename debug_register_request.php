<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/Bootcamp';
$cookieFile = __DIR__ . '/debug_cookie.txt';

$options = [
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\n",
        'ignore_errors' => true,
    ],
];

$context = stream_context_create($options);
$csrfResponse = file_get_contents($base . '/ajax/csrf.php', false, $context);
if ($csrfResponse === false) {
    echo "CSRF request failed.\n";
    exit(1);
}

$csrf = json_decode($csrfResponse, true);
if (empty($csrf['csrf_token'])) {
    echo "No CSRF token returned:\n" . $csrfResponse . "\n";
    exit(1);
}

echo "CSRF token OK: " . substr($csrf['csrf_token'], 0, 8) . "...\n";

$payload = [
    'csrf_token' => $csrf['csrf_token'],
    'first_name' => 'Test',
    'last_name' => 'User',
    'gender' => 'Male',
    'dob' => '2010-01-01',
    'school' => 'Test Primary',
    'class_level' => 'JSS1',
    'nationality' => 'Nigeria',
    'address' => '123 Test Street',
    'parent_name' => 'Parent User',
    'relationship' => 'Father',
    'occupation' => 'Engineer',
    'phone' => '08012345678',
    'whatsapp' => '08012345678',
    'email' => 'testuser+' . time() . '@example.com',
    'emergency_contact' => '08087654321',
    'course' => 'Web',
    'course_id' => 1,
    'course_fee' => 30000,
    'session' => 'Morning',
    'tshirt_size' => 'M',
    'payment_method' => 'offline',
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content' => json_encode($payload),
        'ignore_errors' => true,
    ],
];
$context = stream_context_create($options);
$response = file_get_contents($base . '/ajax/register.php', false, $context);
if ($response === false) {
    echo "Register request failed.\n";
    exit(1);
}

echo "Register response:\n";
echo $response . "\n";
