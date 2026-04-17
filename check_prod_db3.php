<?php
$host = '62.146.181.70';
$port = 3370;
$db   = 'citrobbd';
$user = 'user';
$pass = 'admin@123';

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
    $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

echo "=== COLUMNAS EXACTAS de cart_items ===\n";
$cols = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='citrobbd' AND TABLE_NAME='cart_items' ORDER BY ORDINAL_POSITION")->fetchAll();
foreach ($cols as $c) echo "  {$c['COLUMN_NAME']} ({$c['DATA_TYPE']}) default={$c['COLUMN_DEFAULT']}\n";
echo "\n";

// Intentar insertar un item para probar
echo "=== TEST: Insertar item en carrito (cart_id=1, product_id=1) ===\n";
try {
    // Primero ver si hay un item
    $existing = $pdo->prepare("SELECT * FROM cart_items WHERE cart_id=1 AND product_id=1");
    $existing->execute();
    $row = $existing->fetch();
    echo "  Existing: " . ($row ? json_encode($row) : 'NINGUNO') . "\n";
    
    // Intentar con 'quantity'
    echo "\n  Test INSERT con 'quantity': ";
    try {
        $pdo->exec("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (1, 1, 1)");
        echo "OK\n";
        $pdo->exec("DELETE FROM cart_items WHERE cart_id=1 AND product_id=1");
    } catch (PDOException $e) {
        echo "ERROR - " . $e->getMessage() . "\n";
    }
    
    // Intentar con 'qty'
    echo "  Test INSERT con 'qty': ";
    try {
        $pdo->exec("INSERT INTO cart_items (cart_id, product_id, qty) VALUES (1, 1, 1)");
        echo "OK\n";
        $pdo->exec("DELETE FROM cart_items WHERE cart_id=1 AND product_id=1");
    } catch (PDOException $e) {
        echo "ERROR - " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
