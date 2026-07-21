<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function getSetting($pdo, $key) {
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}
