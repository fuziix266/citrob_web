<?php
declare(strict_types=1);

namespace StoreAdmin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class ProductController extends AbstractActionController
{
    private const LIMIT = 15;
    private const UPLOAD_DIR = 'public/img/productos';
    private const UPLOAD_URL = '/img/productos';

    public function __construct(
        private AdminAuthService $auth,
        private DbService $db
    ) {}

    public function indexAction(): ViewModel|\Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));

        $page   = max(1, (int)($this->params()->fromQuery('page', 1)));
        $search = trim((string)$this->params()->fromQuery('search', ''));
        $catId  = (int)$this->params()->fromQuery('category_id', 0);
        $offset = ($page - 1) * self::LIMIT;

        $where = ['1=1']; $params = [];
        if ($search) { $where[] = 'p.name LIKE ?'; $params[] = "%$search%"; }
        if ($catId)  { $where[] = 'p.category_id = ?'; $params[] = $catId; }
        $w = implode(' AND ', $where);

        $total   = (int)$this->db->queryOne("SELECT COUNT(*) AS c FROM products p WHERE $w", $params)['c'];
        $products = $this->db->query(
            "SELECT p.*, c.name AS cat FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE $w ORDER BY p.id DESC LIMIT " . self::LIMIT . " OFFSET $offset",
            $params
        );
        $categories = $this->db->query('SELECT id, name FROM categories WHERE active=1 ORDER BY sort_order');

        return new ViewModel([
            'products'   => $products,
            'categories' => $categories,
            'total'      => $total,
            'pages'      => max(1, (int)ceil($total / self::LIMIT)),
            'page'       => $page,
            'search'     => $search,
            'catId'      => $catId,
            'admin'      => $this->auth->getCurrentAdmin(),
        ]);
    }

    public function editAction(): ViewModel|\Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));
        $id = (int)$this->params()->fromRoute('id', 0);
        $product = $id ? $this->db->queryOne('SELECT * FROM products WHERE id=?', [$id]) : null;
        $categories = $this->db->query('SELECT id, name FROM categories WHERE active=1 ORDER BY sort_order');

        if ($this->getRequest()->isPost()) {
            $d = $this->getRequest()->getPost();
            $data = [
                'name'        => trim($d['name'] ?? ''),
                'category_id' => (int)($d['category_id'] ?? 0),
                'type'        => trim($d['type'] ?? ''),
                'price'       => (float)($d['price'] ?? 0),
                'stock'       => (int)($d['stock'] ?? 0),
                'rating'      => (float)($d['rating'] ?? 0),
                'reviews'     => (int)($d['reviews'] ?? 0),
                'badge'       => trim($d['badge'] ?? ''),
                'badge_color' => trim($d['badge_color'] ?? 'green'),
                'image_url'   => $product['image_url'] ?? '', // mantener actual por defecto
                'description' => trim($d['description'] ?? ''),
                'active'      => (int)($d['active'] ?? 1),
                'featured'    => (int)($d['featured'] ?? 0),
            ];

            // Procesar archivo subido
            $files = $this->getRequest()->getFiles();
            $file  = $files['image_file'] ?? null;

            if ($file && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
                $imageUrl = $this->handleImageUpload($file, $id ?: null);
                if ($imageUrl) {
                    // Borrar imagen anterior si existía y es diferente
                    if (!empty($product['image_url']) && $product['image_url'] !== $imageUrl) {
                        $this->deleteImageFile($product['image_url']);
                    }
                    $data['image_url'] = $imageUrl;
                }
            }

            if ($id) {
                $this->db->execute(
                    'UPDATE products SET name=?,category_id=?,type=?,price=?,stock=?,rating=?,reviews=?,badge=?,badge_color=?,image_url=?,description=?,active=?,featured=? WHERE id=?',
                    [...array_values($data), $id]
                );
            } else {
                $this->db->execute(
                    'INSERT INTO products (name,category_id,type,price,stock,rating,reviews,badge,badge_color,image_url,description,active,featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                    array_values($data)
                );
                // Si se subió imagen y era producto nuevo, renombrar con el ID real
                $newId = (int)$this->db->lastInsertId();
                if (!empty($data['image_url']) && $newId) {
                    $newUrl = $this->renameImageToId($data['image_url'], $newId);
                    if ($newUrl && $newUrl !== $data['image_url']) {
                        $this->db->execute('UPDATE products SET image_url=? WHERE id=?', [$newUrl, $newId]);
                    }
                }
            }
            return $this->redirect()->toRoute('admin-products');
        }

        $vm = new ViewModel(['product' => $product, 'categories' => $categories, 'admin' => $this->auth->getCurrentAdmin()]);
        $vm->setTemplate('store-admin/product/edit');
        return $vm;
    }

    public function deleteAction(): \Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id) {
            // Obtener imagen antes de borrar el producto
            $product = $this->db->queryOne('SELECT image_url FROM products WHERE id=?', [$id]);
            if ($product && !empty($product['image_url'])) {
                $this->deleteImageFile($product['image_url']);
            }
            $this->db->execute('DELETE FROM products WHERE id=?', [$id]);
        }
        return $this->redirect()->toRoute('admin-products');
    }

    public function toggleAction(): \Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id) $this->db->execute('UPDATE products SET active = NOT active WHERE id=?', [$id]);
        return $this->redirect()->toRoute('admin-products');
    }

    /**
     * Sube una imagen al directorio público y retorna la URL relativa.
     */
    private function handleImageUpload(array $file, ?int $productId): ?string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime    = $file['type'] ?? '';

        if (!in_array($mime, $allowed, true)) {
            return null;
        }

        // Crear directorio si no existe
        $dir = self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Determinar extensión
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };

        // Nombre del archivo: usar ID si existe, sino timestamp único
        $basename = $productId ? (string)$productId : 'tmp_' . time() . '_' . bin2hex(random_bytes(4));
        $filename = $basename . '.' . $ext;
        $filepath = $dir . '/' . $filename;

        // Mover archivo subido
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return self::UPLOAD_URL . '/' . $filename;
        }

        return null;
    }

    /**
     * Renombra una imagen temporal al ID real del producto.
     */
    private function renameImageToId(string $currentUrl, int $newId): ?string
    {
        $currentPath = 'public' . $currentUrl;
        if (!file_exists($currentPath)) {
            return $currentUrl;
        }

        $ext     = pathinfo($currentPath, PATHINFO_EXTENSION);
        $newName = $newId . '.' . $ext;
        $newPath = self::UPLOAD_DIR . '/' . $newName;
        $newUrl  = self::UPLOAD_URL . '/' . $newName;

        if (rename($currentPath, $newPath)) {
            return $newUrl;
        }

        return $currentUrl;
    }

    /**
     * Elimina un archivo de imagen del disco.
     */
    private function deleteImageFile(string $imageUrl): void
    {
        // Solo borrar si está dentro de nuestro directorio de uploads
        if (strpos($imageUrl, self::UPLOAD_URL) !== 0) {
            return;
        }

        $path = 'public' . $imageUrl;
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }
    }
}
