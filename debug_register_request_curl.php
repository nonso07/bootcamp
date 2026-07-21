<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/Bootcamp';
$cookieFile = __DIR__ . '/debug_cookie.txt';

function curlRequest(string $url, array $options = []): array {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    foreach ($options as $opt => $value) {
        curl_setopt($ch, $opt, $value);
    }

    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    if ($body === false) {
        echo 'Curl error: ' . curl_error($ch) . "\n";
    }
    curl_close($ch);
    return ['body' => $body, 'info' => $info];
}

if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

$csrf = curlRequest($base . '/ajax/csrf.php', [CURLOPT_HTTPGET => true]);
if ($csrf['body'] === false) {
    echo "Failed to GET CSRF.\n";
    exit(1);
}

$csrfData = json_decode($csrf['body'], true);
if (empty($csrfData['csrf_token'])) {
    echo "Invalid CSRF response:\n" . $csrf['body'] . "\n";
    exit(1);
}

echo "Got CSRF token: " . substr($csrfData['csrf_token'], 0, 8) . "...\n";

$payload = [
    'csrf_token' => $csrfData['csrf_token'],
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

$register = curlRequest($base . '/ajax/register.php', [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
]);

echo "Register response status: " . $register['info']['http_code'] . "\n";
echo $register['body'] . "\n";
