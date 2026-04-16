<?php
// Verifica estructura real de orders y order_items
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=citrobbd;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
header('Content-Type: text/plain; charset=utf-8');
echo "=== orders ===\n";
print_r($pdo->query("DESCRIBE orders")->fetchAll());
echo "\n=== order_items ===\n";
print_r($pdo->query("DESCRIBE order_items")->fetchAll());
echo "\n=== carts ===\n";
print_r($pdo->query("DESCRIBE carts")->fetchAll());
echo "\n=== cart_items ===\n";
print_r($pdo->query("DESCRIBE cart_items")->fetchAll());
echo "\n=== admins ===\n";
print_r($pdo->query("DESCRIBE admins")->fetchAll());
