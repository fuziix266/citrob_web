<?php
declare(strict_types=1);

namespace StoreAdmin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class ApiController extends AbstractActionController
{
    public function __construct(private AdminAuthService $auth, private DbService $db) {}

    public function indexAction(): JsonModel
    {
        $resource = $this->params()->fromRoute('resource', '');
        $action   = $this->params()->fromRoute('action', 'list');
        $method   = $this->getRequest()->getMethod();

        if (!$this->auth->isLoggedIn()) {
            return new JsonModel(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        return match ($resource) {
            'products'   => $this->handleProducts($action, $method),
            'categories' => $this->handleCategories($action, $method),
            default      => new JsonModel(['success' => false, 'error' => 'Not found']),
        };
    }

    private function handleProducts(string $action, string $method): JsonModel
    {
        if ($method === 'GET') {
            $page   = max(1,(int)$this->params()->fromQuery('page', 1));
            $limit  = 10;
            $offset = ($page-1)*$limit;
            $search = trim((string)$this->params()->fromQuery('search',''));

            $where = ['1=1']; $params = [];
            if ($search) { $where[] = 'name LIKE ?'; $params[] = "%$search%"; }
            $w = implode(' AND ',$where);

            $total = (int)$this->db->queryOne("SELECT COUNT(*) AS c FROM products WHERE $w", $params)['c'];
            $items = $this->db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE $w ORDER BY p.id DESC LIMIT $limit OFFSET $offset", $params);
            return new JsonModel(['success' => true, 'data' => $items, 'total' => $total, 'page' => $page]);
        }

        $d = $this->getRequest()->getPost()->toArray();
        $id = (int)($d['id'] ?? 0);

        if ($action === 'delete' && $id) {
            $this->db->execute('DELETE FROM products WHERE id=?', [$id]);
            return new JsonModel(['success' => true]);
        }

        if ($action === 'toggle' && $id) {
            $this->db->execute('UPDATE products SET active = NOT active WHERE id=?', [$id]);
            return new JsonModel(['success' => true]);
        }

        return new JsonModel(['success' => false, 'error' => 'Unknown action']);
    }

    private function handleCategories(string $action, string $method): JsonModel
    {
        $items = $this->db->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count FROM categories c ORDER BY sort_order');
        return new JsonModel(['success' => true, 'data' => $items]);
    }
}
