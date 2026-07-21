<?php
$pdo = new PDO('mysql:host=localhost;dbname=habatech_bootcamp;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = ['students','parents','courses','registrations','invoices','payments','invoice_items','activity_logs'];
foreach ($tables as $table) {
    echo "\nTABLE $table:\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM '.$table);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo $col['Field'] . '\t' . $col['Type'] . '\t' . $col['Null'] . '\t' . $col['Key'] . '\n';
    }
    echo "\nSHOW CREATE TABLE $table:\n";
    $stmt = $pdo->query('SHOW CREATE TABLE '.$table);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Create Table'] . "\n";
}
