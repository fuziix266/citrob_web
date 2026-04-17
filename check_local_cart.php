<?php
require 'vendor/autoload.php';
use StoreAdmin\Service\DbService;

$db = new DbService();

echo "=== TABLAS LOCALES ===\n";
$tables = $db->query("SHOW TABLES");
foreach ($tables as $t) echo "  - " . array_values($t)[0] . "\n";

echo "\n=== DESCRIBE cart_items (local) ===\n";
try {
    $cols = $db->query("DESCRIBE cart_items");
    foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']}\n";
} catch (Exception $e) {
    echo "  NO EXISTE: " . $e->getMessage() . "\n";
}

echo "\n=== DESCRIBE carts (local) ===\n";
try {
    $cols = $db->query("DESCRIBE carts");
    foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']}\n";
} catch (Exception $e) {
    echo "  NO EXISTE: " . $e->getMessage() . "\n";
}

echo "\n=== DESCRIBE orders (local) ===\n";
$cols = $db->query("DESCRIBE orders");
foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']}\n";

echo "\n=== DESCRIBE order_items (local) ===\n";
$cols = $db->query("DESCRIBE order_items");
foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']}\n";
