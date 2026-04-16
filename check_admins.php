<?php
$pdo = new PDO('mysql:host=62.146.181.70;port=3370;dbname=citrobbd;charset=utf8mb4', 'user', 'admin@123', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$stmt = $pdo->query('DESCRIBE admins');
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . ' | ' . $col['Type'] . PHP_EOL;
}
