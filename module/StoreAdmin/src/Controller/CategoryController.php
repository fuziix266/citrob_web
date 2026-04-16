<?php
declare(strict_types=1);

namespace StoreAdmin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class CategoryController extends AbstractActionController
{
    public function __construct(private AdminAuthService $auth, private DbService $db) {}

    public function indexAction(): ViewModel|\Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));

        if ($this->getRequest()->isPost()) {
            $d = $this->getRequest()->getPost();
            $action = $d['_action'] ?? 'save';

            if ($action === 'delete') {
                $id = (int)($d['id'] ?? 0);
                if ($id > 1) {
                    // Mover productos a categoría "Todos" (id=1) antes de borrar la categoría
                    $this->db->execute('UPDATE products SET category_id=1 WHERE category_id=?', [$id]);
                    $this->db->execute('DELETE FROM categories WHERE id=?', [$id]);
                }
            } else {
                $id   = (int)($d['id'] ?? 0);
                $name = trim($d['name'] ?? '');
                $slug = trim($d['slug'] ?? '');
                if (!$slug) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                }
                
                $data = [
                    'name' => $name, 
                    'slug' => $slug, 
                    'icon' => trim($d['icon'] ?? 'category'), 
                    'sort_order' => (int)($d['sort_order'] ?? 0), 
                    'active' => (int)($d['active'] ?? 1)
                ];
                
                if ($id) {
                    $this->db->execute('UPDATE categories SET name=?,slug=?,icon=?,sort_order=?,active=? WHERE id=?', [...array_values($data), $id]);
                } else {
                    $this->db->execute('INSERT INTO categories (name,slug,icon,sort_order,active) VALUES (?,?,?,?,?)', array_values($data));
                }
            }
            return $this->redirect()->toRoute('admin-categories');
        }

        $categories = $this->db->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.active=1) AS product_count FROM categories c ORDER BY c.sort_order');
        return new ViewModel(['categories' => $categories, 'admin' => $this->auth->getCurrentAdmin()]);
    }
}
