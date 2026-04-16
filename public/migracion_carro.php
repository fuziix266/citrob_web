<?php
// Script de migración - crea tablas y arregla el carrito
// Solo para uso desde localhost

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=citrobbd;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("<p style='color:red'>❌ Error de conexión: " . $e->getMessage() . "</p>");
}

$sqls = [];

// 1. Verificar columnas de products
$cols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
echo "<h2>Columnas de products: " . implode(', ', $cols) . "</h2>";
$hasTitle = in_array('title', $cols);
$hasName  = in_array('name', $cols);

// 2. Crear tabla carts
$sqls[] = [
    'desc' => 'Crear tabla carts',
    'sql'  => "CREATE TABLE IF NOT EXISTS `carts` (
        `id`         INT          NOT NULL AUTO_INCREMENT,
        `user_id`    INT          NOT NULL,
        `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// 3. Crear tabla cart_items
$sqls[] = [
    'desc' => 'Crear tabla cart_items',
    'sql'  => "CREATE TABLE IF NOT EXISTS `cart_items` (
        `id`         INT          NOT NULL AUTO_INCREMENT,
        `cart_id`    INT          NOT NULL,
        `product_id` INT          NOT NULL,
        `quantity`   INT          NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_cart_product` (`cart_id`, `product_id`),
        CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// 4. Crear tabla orders si no existe
$sqls[] = [
    'desc' => 'Crear tabla orders',
    'sql'  => "CREATE TABLE IF NOT EXISTS `orders` (
        `id`              INT           NOT NULL AUTO_INCREMENT,
        `customer_name`   VARCHAR(255)  NOT NULL,
        `customer_email`  VARCHAR(255)  NOT NULL DEFAULT '',
        `customer_phone`  VARCHAR(50)   NOT NULL DEFAULT '',
        `total_price`     DECIMAL(12,2) NOT NULL DEFAULT 0,
        `status`          VARCHAR(50)   NOT NULL DEFAULT 'pending',
        `hash_id`         VARCHAR(64)   NOT NULL,
        `stock_subtracted` TINYINT(1)  NOT NULL DEFAULT 0,
        `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_hash` (`hash_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// 5. Crear tabla order_items si no existe
$sqls[] = [
    'desc' => 'Crear tabla order_items',
    'sql'  => "CREATE TABLE IF NOT EXISTS `order_items` (
        `id`         INT           NOT NULL AUTO_INCREMENT,
        `order_id`   INT           NOT NULL,
        `product_id` INT           NOT NULL,
        `quantity`   INT           NOT NULL DEFAULT 1,
        `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// 6. Si products tiene 'name' pero no 'title', agregar alias/columna
if ($hasName && !$hasTitle) {
    $sqls[] = [
        'desc' => 'Agregar columna title como alias de name en products',
        'sql'  => "ALTER TABLE `products` ADD COLUMN `title` VARCHAR(255) GENERATED ALWAYS AS (`name`) STORED;"
    ];
}

// Ejecutar todos
echo "<!DOCTYPE html><html><head><meta charset='utf-8'><style>
body{font-family:monospace;background:#111;color:#eee;padding:20px}
.ok{color:#4f4}.fail{color:#f44}.info{color:#fa4}
pre{background:#1a1a1a;padding:8px}
</style><title>Migración Carrito</title></head><body>";
echo "<h1 style='color:#e85d22'>🔧 Migración BD - Carrito CITROB</h1>";

foreach ($sqls as $item) {
    echo "<div style='margin:10px 0;padding:10px;background:#1a1a1a;border:1px solid #333;border-radius:6px'>";
    echo "<p class='info'>⏳ " . htmlspecialchars($item['desc']) . "</p>";
    echo "<pre>" . htmlspecialchars($item['sql']) . "</pre>";
    try {
        $pdo->exec($item['sql']);
        echo "<p class='ok'>✅ Éxito</p>";
    } catch (PDOException $e) {
        echo "<p class='fail'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
}

// Verificar resultado final
echo "<h2 style='color:#09f;margin-top:30px'>✅ Estado final de tablas:</h2>";
$tables = ['products', 'carts', 'cart_items', 'orders', 'order_items'];
echo "<table style='border-collapse:collapse;width:100%'><tr><th style='border:1px solid #444;padding:6px'>Tabla</th><th style='border:1px solid #444;padding:6px'>Columnas</th><th style='border:1px solid #444;padding:6px'>Filas</th></tr>";
foreach ($tables as $t) {
    try {
        $cols  = implode(', ', $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN));
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "<tr><td style='border:1px solid #444;padding:6px;color:#4f4'>$t</td><td style='border:1px solid #444;padding:6px;font-size:11px'>$cols</td><td style='border:1px solid #444;padding:6px'>$count</td></tr>";
    } catch (PDOException $e) {
        echo "<tr><td style='border:1px solid #444;padding:6px;color:#f44'>$t</td><td colspan='2' style='border:1px solid #444;padding:6px;color:#f44'>" . $e->getMessage() . "</td></tr>";
    }
}
echo "</table>";
echo "<p style='color:#aaa;margin-top:20px'>✅ Migración completada. Puedes eliminar este archivo.</p>";
echo "</body></html>";
