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

// Revisar tablas de carrito
echo "=== DESCRIBE carts ===\n";
$cols = $pdo->query("DESCRIBE carts")->fetchAll();
foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";
echo "\n";

echo "=== DESCRIBE cart_items ===\n";
$cols = $pdo->query("DESCRIBE cart_items")->fetchAll();
foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";
echo "\n";

echo "=== FOREIGN KEYS de cart_items ===\n";
$fks = $pdo->query("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='cart_items' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
foreach ($fks as $fk) echo "  {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
echo "\n";

echo "=== FOREIGN KEYS de carts ===\n";
$fks = $pdo->query("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='carts' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
foreach ($fks as $fk) echo "  {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
echo "\n";

// Contar registros
echo "=== DATOS ===\n";
echo "  carts: " . $pdo->query("SELECT COUNT(*) FROM carts")->fetchColumn() . " registros\n";
echo "  cart_items: " . $pdo->query("SELECT COUNT(*) FROM cart_items")->fetchColumn() . " registros\n";
echo "  orders: " . $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() . " registros\n";
echo "  order_items: " . $pdo->query("SELECT COUNT(*) FROM order_items")->fetchColumn() . " registros\n";
