<?php
declare(strict_types=1);

namespace StoreAdmin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class OrderController extends AbstractActionController
{
    public function __construct(private AdminAuthService $auth, private DbService $db) {}

    public function indexAction(): ViewModel|\Laminas\Http\Response
    {
        $this->auth->requireLogin($this->url()->fromRoute('admin-login'));

        $status = (string)$this->params()->fromQuery('status', '');
        $page   = max(1,(int)$this->params()->fromQuery('page', 1));
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        if ($this->getRequest()->isPost()) {
            $d = $this->getRequest()->getPost();
            $valid = ['pending','processing','shipped','delivered','cancelled'];
            if (isset($d['order_id'], $d['new_status']) && in_array($d['new_status'], $valid, true)) {
                $orderId = (int)$d['order_id'];
                $newStatus = $d['new_status'];
                $oldOrder = $this->db->fetchOne('SELECT status FROM orders WHERE id = ?', [$orderId]);
                
                if ($oldOrder) {
                    $oldStatus = $oldOrder['status'];
                    $this->db->execute('UPDATE orders SET status=? WHERE id=?', [$newStatus, $orderId]);
                    
                    // Si pasa de pendiente a procesando/enviado/entregado, restamos stock
                    $approvedStatuses = ['processing', 'shipped', 'delivered'];
                    if ($oldStatus === 'pending' && in_array($newStatus, $approvedStatuses, true)) {
                        $items = $this->db->fetchAll('SELECT product_id, quantity FROM order_items WHERE order_id = ?', [$orderId]);
                        foreach ($items as $item) {
                            $this->db->execute('UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?', [(int)$item['quantity'], (int)$item['product_id']]);
                        }
                    }
                }
            }
            return $this->redirect()->toRoute('admin-orders', [], ['query' => ['status' => $status]]);
        }

        $where = ['1=1']; $params = [];
        if ($status) { $where[] = 'status=?'; $params[] = $status; }
        $w = implode(' AND ', $where);

        $total  = (int)$this->db->queryOne("SELECT COUNT(*) AS c FROM orders WHERE $w", $params)['c'];
        $orders = $this->db->query("SELECT * FROM orders WHERE $w ORDER BY created_at DESC LIMIT $limit OFFSET $offset", $params);
        $counts = $this->db->query('SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status');
        $statusCounts = array_column($counts, 'cnt', 'status');

        return new ViewModel([
            'orders'       => $orders,
            'total'        => $total,
            'pages'        => max(1,(int)ceil($total/$limit)),
            'page'         => $page,
            'status'       => $status,
            'statusCounts' => $statusCounts,
            'admin'        => $this->auth->getCurrentAdmin(),
        ]);
    }
}
