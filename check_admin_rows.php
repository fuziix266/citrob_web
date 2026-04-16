<?php
$pdo = new PDO('mysql:host=62.146.181.70;port=3370;dbname=citrobbd;charset=utf8mb4', 'user', 'admin@123', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
// Ver todos los usuarios existentes
$stmt = $pdo->query('SELECT id, username, name, email, active FROM admins LIMIT 10');
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo implode(' | ', $row) . PHP_EOL;
}
