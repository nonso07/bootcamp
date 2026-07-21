<?php
$pdo = new PDO('mysql:host=localhost;dbname=habatech_bootcamp;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$stmt = $pdo->query('SHOW COLUMNS FROM schools');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . ' ' . $col['Type'] . "\n";
}
