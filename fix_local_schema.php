<?php
// Normalizar BD local: renombrar quantity -> qty en cart_items para coincidir con producción
require 'vendor/autoload.php';
use StoreAdmin\Service\DbService;

$db = new DbService();

try {
    $db->execute("ALTER TABLE cart_items CHANGE COLUMN quantity qty int(10) unsigned NOT NULL DEFAULT 1");
    echo "OK: columna 'quantity' renombrada a 'qty' en cart_items\n";
    
    // Verificar
    $cols = $db->query("DESCRIBE cart_items");
    foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']}\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
