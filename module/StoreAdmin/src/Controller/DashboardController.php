<?php
declare(strict_types=1);

namespace StoreAdmin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class DashboardController extends AbstractActionController
{
    public function __construct(
        private AdminAuthService $auth,
        private DbService $db
    ) {}

    public function indexAction(): ViewModel|\Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));

        $stats = [
            'products'   => (int)($this->db->queryOne('SELECT COUNT(*) AS c FROM products WHERE active=1')['c'] ?? 0),
            'categories' => (int)($this->db->queryOne('SELECT COUNT(*) AS c FROM categories WHERE active=1')['c'] ?? 0),
            'orders'     => (int)($this->db->queryOne('SELECT COUNT(*) AS c FROM orders')['c'] ?? 0),
            'inventory'  => (float)($this->db->queryOne('SELECT SUM(price*stock) AS c FROM products WHERE active=1')['c'] ?? 0),
        ];

        $recent = $this->db->query(
            'SELECT p.*, c.name AS cat FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC LIMIT 8'
        );

        $catDist = $this->db->query(
            'SELECT c.name, COUNT(p.id) AS cnt FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.active=1 WHERE c.id > 1 GROUP BY c.id ORDER BY cnt DESC'
        );

        $vm = new ViewModel([
            'stats'   => $stats,
            'recent'  => $recent,
            'catDist' => $catDist,
            'admin'   => $this->auth->getCurrentAdmin(),
        ]);
        $vm->setTemplate('store-admin/dashboard/index');
        return $vm;
    }
}
