<?php
// Verificar y corregir esquema de la tabla orders en producción
$pdo = new PDO(
    'mysql:host=62.146.181.70;port=3370;dbname=citrobbd;charset=utf8mb4',
    'user',
    'admin@123',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Columnas actuales de 'orders' en PRODUCCIÓN ===\n";
$cols = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_ASSOC);
$colNames = [];
foreach ($cols as $c) {
    echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']}\n";
    $colNames[] = $c['Field'];
}

if (!in_array('hash_id', $colNames)) {
    echo "\n⚠ Columna 'hash_id' NO EXISTE. Agregándola...\n";
    $pdo->exec('ALTER TABLE orders ADD COLUMN hash_id VARCHAR(64) DEFAULT NULL AFTER status');
    echo "✓ Columna hash_id agregada.\n";
    
    // Agregar índice único
    $pdo->exec('ALTER TABLE orders ADD UNIQUE INDEX idx_hash_id (hash_id)');
    echo "✓ Índice único idx_hash_id creado.\n";
} else {
    echo "\n✓ Columna 'hash_id' YA EXISTE.\n";
}

// Verificar FK constraint de customer_id
echo "\n=== Verificando FK constraints ===\n";
$fks = $pdo->query("
    SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'citrobbd' 
    AND TABLE_NAME = 'orders' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($fks as $fk) {
    echo "  {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
}

echo "\n=== Esquema final ===\n";
$cols = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  {$c['Field']} | {$c['Type']}\n";
}

echo "\n✅ Listo.\n";
