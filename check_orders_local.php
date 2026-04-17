<?php
require 'vendor/autoload.php';
use StoreAdmin\Service\DbService;

$db = new DbService();

// Verificar orders local
echo "=== DESCRIBE orders (local) ===\n";
$cols = $db->query("DESCRIBE orders");
foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";

echo "\n=== Verificar si hash_id existe ===\n";
$hasHashId = false;
foreach ($cols as $c) {
    if ($c['Field'] === 'hash_id') { $hasHashId = true; break; }
}
echo "  hash_id existe: " . ($hasHashId ? 'SI' : 'NO') . "\n";

echo "\n=== Verificar si stock_subtracted existe ===\n";
$hasStock = false;
foreach ($cols as $c) {
    if ($c['Field'] === 'stock_subtracted') { $hasStock = true; break; }
}
echo "  stock_subtracted existe: " . ($hasStock ? 'SI' : 'NO') . "\n";

// Probar insert con hash_id
if ($hasHashId) {
    echo "\n=== Test INSERT con hash_id: OK ===\n";
} else {
    echo "\n=== hash_id NO existe, el checkout fallará con INSERT ===\n";
    echo "  Agregando columna hash_id...\n";
    try {
        $db->execute("ALTER TABLE orders ADD COLUMN hash_id varchar(64) DEFAULT NULL UNIQUE AFTER status");
        echo "  hash_id agregada correctamente\n";
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
