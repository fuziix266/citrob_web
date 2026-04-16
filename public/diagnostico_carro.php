<?php
// Diagnóstico completo del carrito de compras - SOLO USO LOCAL
header('Content-Type: text/html; charset=utf-8');

function conectar(string $host, string $port, string $db, string $user, string $pass): ?PDO {
    try {
        return new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        return null;
    }
}

function checkTable(PDO $pdo, string $table): array {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll();
        $count = $pdo->query("SELECT COUNT(*) AS cnt FROM `$table`")->fetch()['cnt'];
        return ['exists' => true, 'columns' => array_column($cols, 'Field'), 'rows' => $count];
    } catch (PDOException $e) {
        return ['exists' => false, 'error' => $e->getMessage()];
    }
}

// Config local
$local  = conectar('localhost', '3306', 'citrobbd', 'root', '');
// Config producción
$remote = conectar('62.146.181.70', '3370', 'citrobbd', 'citrob_user', 'Tu_Password_Aqui');

$tables = ['admins', 'products', 'categories', 'carts', 'cart_items', 'orders', 'order_items'];
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Diagnóstico Carrito CITROB</title>
<style>
body{font-family:monospace;background:#111;color:#eee;padding:20px;}
h2{color:#f90;border-bottom:1px solid #333;padding-bottom:5px;}
table{border-collapse:collapse;width:100%;margin-bottom:20px;}
th,td{border:1px solid #444;padding:6px 10px;text-align:left;}
th{background:#222;color:#aaa;}
.ok{color:#4f4;}.fail{color:#f44;}.warn{color:#fa4;}
pre{background:#1a1a1a;padding:10px;overflow:auto;font-size:12px;}
.section{background:#1a1a1a;border:1px solid #333;border-radius:8px;padding:15px;margin-bottom:20px;}
</style>
</head>
<body>
<h1 style="color:#e85d22">🛒 Diagnóstico Completo - Carrito CITROB</h1>

<?php foreach (['Local (XAMPP)' => $local, 'Producción (62.146.181.70)' => $remote] as $label => $pdo): ?>
<div class="section">
<h2><?= $label ?></h2>
<?php if (!$pdo): ?>
    <p class="fail">❌ Sin conexión a la base de datos</p>
<?php else: ?>
<table>
<tr><th>Tabla</th><th>Existe</th><th>Columnas</th><th>Registros</th></tr>
<?php foreach ($tables as $t):
    $info = checkTable($pdo, $t);
    $exists = $info['exists'] ? "<span class='ok'>✅ SÍ</span>" : "<span class='fail'>❌ NO</span>";
    $cols = $info['exists'] ? implode(', ', $info['columns']) : "<span class='fail'>" . ($info['error'] ?? '') . "</span>";
    $rows = $info['exists'] ? $info['rows'] : '-';
?>
<tr><td><?= $t ?></td><td><?= $exists ?></td><td style="font-size:11px"><?= $cols ?></td><td><?= $rows ?></td></tr>
<?php endforeach; ?>
</table>

<?php if (isset($info) && $pdo):
    // Mostrar productos de ejemplo
    try {
        $prods = $pdo->query("SELECT id, title, price, stock, active FROM products LIMIT 5")->fetchAll();
        echo "<h3 style='color:#09f'>Productos (primeros 5):</h3><pre>" . print_r($prods, true) . "</pre>";
    } catch (Exception $e) { echo "<p class='warn'>No se pudo leer products: " . $e->getMessage() . "</p>"; }
    
    // Mostrar carritos
    try {
        $carts = $pdo->query("SELECT * FROM carts LIMIT 5")->fetchAll();
        echo "<h3 style='color:#09f'>Carritos (primeros 5):</h3><pre>" . print_r($carts, true) . "</pre>";
    } catch (Exception $e) { echo "<p class='warn'>No se pudo leer carts: " . $e->getMessage() . "</p>"; }
    
    // Mostrar items de carrito
    try {
        $items = $pdo->query("SELECT ci.*, p.title FROM cart_items ci LEFT JOIN products p ON p.id = ci.product_id LIMIT 10")->fetchAll();
        echo "<h3 style='color:#09f'>Items en carrito:</h3><pre>" . print_r($items, true) . "</pre>";
    } catch (Exception $e) { echo "<p class='warn'>No se pudo leer cart_items: " . $e->getMessage() . "</p>"; }
    
    // Mostrar admins
    try {
        $admins = $pdo->query("SELECT id, username, name, email, is_admin, active FROM admins")->fetchAll();
        echo "<h3 style='color:#09f'>Usuarios (admins):</h3><pre>" . print_r($admins, true) . "</pre>";
    } catch (Exception $e) { echo "<p class='warn'>No se pudo leer admins: " . $e->getMessage() . "</p>"; }
endif; ?>
<?php endif; ?>
</div>
<?php endforeach; ?>

<div class="section">
<h2>Sesión actual</h2>
<?php
session_name('citrob_admin');
if (session_status() === PHP_SESSION_NONE) session_start();
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
?>
</div>

<div class="section">
<h2>Test directo de API del carrito</h2>
<?php
session_name('citrob_admin');
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['citrob_admin_id'] ?? null;
echo "<p>Usuario en sesión: <strong>" . ($userId ?? 'NO HAY SESIÓN') . "</strong></p>";
if ($local && $userId) {
    try {
        $cart = $local->prepare("SELECT id FROM carts WHERE user_id = ?");
        $cart->execute([$userId]);
        $row = $cart->fetch();
        echo "<p class='ok'>Carrito encontrado: " . ($row ? "ID " . $row['id'] : "sin carrito aún") . "</p>";
        
        if ($row) {
            $items = $local->prepare("SELECT ci.*, p.title FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ?");
            $items->execute([$row['id']]);
            echo "<pre>Items: " . print_r($items->fetchAll(), true) . "</pre>";
        }
    } catch (Exception $e) {
        echo "<p class='fail'>Error: " . $e->getMessage() . "</p>";
    }
}
?>
</div>
</body>
</html>
