<?php
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=citrobbd;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

try {
    $pdo->exec("ALTER TABLE orders DROP FOREIGN KEY fk_order_customer");
    echo "Foreign key fk_order_customer eliminada.\n";
} catch (Exception $e) {
    echo "No se pudo eliminar la FK, tal vez no existe: " . $e->getMessage() . "\n";
}
