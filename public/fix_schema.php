<?php
header('Content-Type: text/plain; charset=utf-8');

$pdo = new PDO("mysql:host=localhost;port=3306;dbname=citrobbd;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Mostrar columnas de products
echo "=== COLUMNAS DE products ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll();
foreach ($cols as $c) {
    echo "[{$c['Field']}] {$c['Type']} NULL={$c['Null']} DEF={$c['Default']}\n";
}

// Agregar hash_id a orders si no existe
echo "\n=== AGREGAR hash_id A orders ===\n";
$orderCols = array_column($pdo->query("SHOW COLUMNS FROM orders")->fetchAll(), 'Field');
if (!in_array('hash_id', $orderCols)) {
    $pdo->exec("ALTER TABLE orders ADD COLUMN hash_id VARCHAR(64) NULL UNIQUE AFTER status");
    echo "✅ Columna hash_id agregada a orders\n";
} else {
    echo "ℹ️ hash_id ya existe\n";
}

// Agregar title como columna generada si products tiene 'name' pero no 'title'
echo "\n=== VERIFICAR title en products ===\n";
$prodCols = array_column($cols, 'Field');
if (in_array('name', $prodCols) && !in_array('title', $prodCols)) {
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN title VARCHAR(255) GENERATED ALWAYS AS (`name`) STORED");
        echo "✅ Columna title (generada) agregada a products\n";
    } catch (PDOException $e) {
        echo "⚠️ Error al agregar title: " . $e->getMessage() . "\n";
    }
} elseif (in_array('title', $prodCols)) {
    echo "ℹ️ title ya existe en products\n";
} else {
    echo "⚠️ products no tiene columna 'name' ni 'title' — revisar\n";
}

echo "\n=== COLUMNAS ACTUALIZADAS orders ===\n";
$orderCols2 = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll();
foreach ($orderCols2 as $c) {
    echo "[{$c['Field']}] {$c['Type']}\n";
}

echo "\n=== PRODUCTOS EJEMPLO ===\n";
$prods = $pdo->query("SELECT * FROM products LIMIT 3")->fetchAll();
foreach ($prods as $p) {
    echo "ID={$p['id']} name=" . ($p['name'] ?? $p['title'] ?? '?') . " price={$p['price']}\n";
}
