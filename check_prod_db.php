<?php
// Script de diagnóstico para BD de producción
$host = '62.146.181.70';
$port = 3370;
$db   = 'citrobbd';
$user = 'user';
$pass = 'admin@123';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "=== CONEXION OK ===\n\n";

    // 1. Listar tablas
    echo "=== TABLAS ===\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) echo "  - $t\n";
    echo "\n";

    // 2. Estructura de orders
    if (in_array('orders', $tables)) {
        echo "=== DESCRIBE orders ===\n";
        $cols = $pdo->query("DESCRIBE orders")->fetchAll();
        foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";
        echo "\n";
    } else {
        echo "*** TABLA 'orders' NO EXISTE ***\n\n";
    }

    // 3. Estructura de order_items
    if (in_array('order_items', $tables)) {
        echo "=== DESCRIBE order_items ===\n";
        $cols = $pdo->query("DESCRIBE order_items")->fetchAll();
        foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";
        echo "\n";
    } else {
        echo "*** TABLA 'order_items' NO EXISTE ***\n\n";
    }

    // 4. Estructura de customers
    if (in_array('customers', $tables)) {
        echo "=== DESCRIBE customers ===\n";
        $cols = $pdo->query("DESCRIBE customers")->fetchAll();
        foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";
        echo "\n";
    } else {
        echo "*** TABLA 'customers' NO EXISTE ***\n\n";
    }

    // 5. Productos activos
    echo "=== PRODUCTOS ACTIVOS ===\n";
    $count = $pdo->query("SELECT COUNT(*) AS c FROM products WHERE active=1")->fetch();
    echo "  Total productos activos: {$count['c']}\n\n";

    // 6. Categorias
    echo "=== CATEGORIAS ===\n";
    $cats = $pdo->query("SELECT id, name, slug, active FROM categories ORDER BY sort_order")->fetchAll();
    foreach ($cats as $c) echo "  [{$c['id']}] {$c['name']} (slug:{$c['slug']}, active:{$c['active']})\n";
    echo "\n";

    // 7. Revisar foreign keys de order_items
    if (in_array('order_items', $tables)) {
        echo "=== FOREIGN KEYS de order_items ===\n";
        $fks = $pdo->query("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='order_items' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
        foreach ($fks as $fk) echo "  {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        echo "\n";
    }

} catch (PDOException $e) {
    echo "ERROR DE CONEXION: " . $e->getMessage() . "\n";
}
