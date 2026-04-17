<?php

declare(strict_types=1);

namespace Store\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use StoreAdmin\Service\DbService;

class OrderPublicController extends AbstractActionController
{
    public function __construct(private DbService $db) {}

    public function indexAction(): ViewModel
    {
        $hash = $this->params()->fromRoute('hash', '');
        
        if (empty($hash)) {
            return $this->notFoundAction();
        }

        $order = $this->db->queryOne('SELECT * FROM orders WHERE hash_id = ?', [$hash]);

        if (!$order) {
            return $this->notFoundAction();
        }

        $items = $this->db->query('
            SELECT oi.*, p.name, p.image_url 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ', [$order['id']]);

        $vm = new ViewModel([
            'order' => $order,
            'items' => $items
        ]);
        
        $vm->setTemplate('store/order/public');
        return $vm;
    }
}
