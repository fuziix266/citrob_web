<?php
/**
 * Script de migración de imágenes de productos
 * Descarga las imágenes desde URLs externas y las guarda localmente
 * Actualiza image_url en la BD a la ruta relativa /img/products/{id}.jpg
 */

$host   = '62.146.181.70';
$port   = '3370';
$dbname = 'citrobbd';
$user   = 'user';
$pass   = 'admin@123';

$imgDir = __DIR__ . '/../public/img/products';
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0755, true);
    echo "📁 Directorio creado: $imgDir\n";
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    die("❌ Error BD: " . $e->getMessage() . "\n");
}

// Obtener todos los productos con imagen externa
$products = $pdo->query("SELECT id, name, image_url FROM products WHERE image_url IS NOT NULL AND image_url != '' ORDER BY id")->fetchAll();

echo "📦 Total productos con imagen: " . count($products) . "\n\n";

$ok = 0; $fail = 0;

foreach ($products as $p) {
    $id       = $p['id'];
    $name     = $p['name'];
    $url      = $p['image_url'];

    // Si ya es una ruta local, saltar
    if (str_starts_with($url, '/img/')) {
        echo "⏭  [{$id}] Ya es local: {$url}\n";
        continue;
    }

    // Detectar extensión (por defecto .jpg si no se puede determinar)
    $ext = 'jpg';
    $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
    if (!empty($pathInfo['extension'])) {
        $ext = strtolower($pathInfo['extension']);
        // Normalizar extensiones
        if ($ext === 'jpeg') $ext = 'jpg';
        if (!in_array($ext, ['jpg','png','webp','gif'])) $ext = 'jpg';
    }

    $localPath    = "{$imgDir}/{$id}.{$ext}";
    $publicPath   = "/img/products/{$id}.{$ext}";

    echo "⬇  [{$id}] {$name}\n    URL: {$url}\n";

    // Descargar la imagen
    $ctx = stream_context_create([
        'http' => [
            'timeout'    => 15,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'follow_location' => true,
            'header'     => "Accept: image/*\r\n",
        ],
        'https' => [
            'timeout'    => 15,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ],
    ]);

    $imageData = @file_get_contents($url, false, $ctx);

    if ($imageData === false || strlen($imageData) < 100) {
        echo "    ❌ Falló la descarga\n\n";
        $fail++;
        continue;
    }

    file_put_contents($localPath, $imageData);
    $sizeKb = round(filesize($localPath) / 1024, 1);
    echo "    ✅ Guardada ({$sizeKb} KB) → {$publicPath}\n";

    // Actualizar BD
    $stmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
    $stmt->execute([$publicPath, $id]);
    echo "    📝 BD actualizada\n\n";

    $ok++;
    usleep(200000); // 200ms entre descargas para no saturar
}

echo "═══════════════════════════════\n";
echo "✅ Descargadas: {$ok}\n";
echo "❌ Fallidas:    {$fail}\n";
echo "Imágenes en: {$imgDir}\n";
