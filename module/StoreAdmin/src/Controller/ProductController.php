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
                'image_url'   => trim($d['image_url'] ?? ''),
                'description' => trim($d['description'] ?? ''),
                'active'      => (int)($d['active'] ?? 1),
                'featured'    => (int)($d['featured'] ?? 0),
            ];
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
        if ($id) $this->db->execute('DELETE FROM products WHERE id=?', [$id]);
        return $this->redirect()->toRoute('admin-products');
    }

    public function toggleAction(): \Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id) $this->db->execute('UPDATE products SET active = NOT active WHERE id=?', [$id]);
        return $this->redirect()->toRoute('admin-products');
    }
}
