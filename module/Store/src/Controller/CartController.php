<?php

declare(strict_types=1);

namespace Store\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class CartController extends AbstractActionController
{
    public function __construct(
        private AdminAuthService $auth,
        private DbService $db
    ) {}

    /** Devuelve el ID del admin/usuario en sesión, o null si no está logueado */
    private function getUserId(): ?int
    {
        $admin = $this->auth->getCurrentAdmin();
        return $admin ? (int)$admin['id'] : null;
    }

    /**
     * Obtiene (o crea) el carrito para un usuario.
     * La tabla carts tiene: id, user_id, created_at
     */
    private function getCartId(int $userId): int
    {
        $cart = $this->db->queryOne('SELECT id FROM carts WHERE user_id = ?', [$userId]);
        if ($cart) {
            return (int)$cart['id'];
        }
        $this->db->execute('INSERT INTO carts (user_id) VALUES (?)', [$userId]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * GET /shop/api/cart
     * Devuelve los items del carrito del usuario logueado.
     * products: id, name (no sku), price
     */
    public function getAction(): JsonModel
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return new JsonModel(['success' => false, 'error' => 'No autorizado']);
        }

        $cartId = $this->getCartId($userId);
        $items  = $this->db->query('
            SELECT ci.id, ci.product_id, ci.qty AS quantity,
                   p.name AS title,
                   p.price,
                   p.image_url
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
        ', [$cartId]);

        return new JsonModel(['success' => true, 'items' => $items]);
    }

    /**
     * POST /shop/api/cart/add
     * Body JSON: { product_id: int, quantity: int }
     */
    public function addAction(): JsonModel
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return new JsonModel(['success' => false, 'error' => 'No autorizado']);
        }

        if (!$this->getRequest()->isPost()) {
            return new JsonModel(['success' => false, 'error' => 'Método no permitido']);
        }

        $content   = $this->getRequest()->getContent();
        $data      = json_decode($content, true);
        $productId = (int)($data['product_id'] ?? 0);
        $quantity  = (int)($data['quantity']   ?? 1);

        if ($productId <= 0 || $quantity === 0) {
            return new JsonModel(['success' => false, 'error' => 'Datos inválidos']);
        }

        // Verificar que el producto existe y está activo
        $product = $this->db->queryOne(
            'SELECT id, name, price, stock FROM products WHERE id = ? AND active = 1',
            [$productId]
        );
        if (!$product) {
            return new JsonModel(['success' => false, 'error' => 'Producto no disponible']);
        }

        $cartId   = $this->getCartId($userId);
        $existing = $this->db->queryOne(
            'SELECT id, qty AS quantity FROM cart_items WHERE cart_id = ? AND product_id = ?',
            [$cartId, $productId]
        );

        if ($existing) {
            $newQty = max(0, $existing['quantity'] + $quantity);
            if ($newQty === 0) {
                $this->db->execute('DELETE FROM cart_items WHERE id = ?', [$existing['id']]);
            } else {
                $this->db->execute(
                    'UPDATE cart_items SET qty = ? WHERE id = ?',
                    [$newQty, $existing['id']]
                );
            }
        } else {
            if ($quantity > 0) {
                $this->db->execute(
                    'INSERT INTO cart_items (cart_id, product_id, qty) VALUES (?, ?, ?)',
                    [$cartId, $productId, $quantity]
                );
            }
        }

        return $this->getAction();
    }

    /**
     * POST /shop/api/cart/remove
     * Body JSON: { product_id: int }
     */
    public function removeAction(): JsonModel
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return new JsonModel(['success' => false, 'error' => 'No autorizado']);
        }

        if (!$this->getRequest()->isPost()) {
            return new JsonModel(['success' => false, 'error' => 'Método no permitido']);
        }

        $content   = $this->getRequest()->getContent();
        $data      = json_decode($content, true);
        $productId = (int)($data['product_id'] ?? 0);

        if ($productId <= 0) {
            return new JsonModel(['success' => false, 'error' => 'Datos inválidos']);
        }

        $cartId = $this->getCartId($userId);
        $this->db->execute(
            'DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?',
            [$cartId, $productId]
        );

        return $this->getAction();
    }

    /**
     * POST /shop/api/cart/clear
     */
    public function clearAction(): JsonModel
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return new JsonModel(['success' => false, 'error' => 'No autorizado']);
        }

        if (!$this->getRequest()->isPost()) {
            return new JsonModel(['success' => false, 'error' => 'Método no permitido']);
        }

        $cartId = $this->getCartId($userId);
        $this->db->execute('DELETE FROM cart_items WHERE cart_id = ?', [$cartId]);

        return $this->getAction();
    }

    /**
     * POST /shop/api/cart/checkout
     * Genera una orden y vacía el carrito.
     * orders: id, customer_id, customer_name, email, phone, address, city, total, status, hash_id, notes
     * order_items: id, order_id, product_id, product_name, qty, unit_price, subtotal
     */
    public function checkoutAction(): JsonModel
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return new JsonModel(['success' => false, 'error' => 'No autorizado']);
        }

        if (!$this->getRequest()->isPost()) {
            return new JsonModel(['success' => false, 'error' => 'Método no permitido']);
        }

        $cartId = $this->getCartId($userId);

        $items = $this->db->query('
            SELECT ci.product_id, ci.qty,
                   p.price AS unit_price,
                   p.name  AS product_name
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
        ', [$cartId]);

        if (empty($items)) {
            return new JsonModel(['success' => false, 'error' => 'Tu carrito está vacío']);
        }

        // Calcular total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['unit_price'] * $item['qty'];
        }

        // Obtener datos del usuario (admin)
        $admin        = $this->db->queryOne(
            'SELECT id, username, name, email FROM admins WHERE id = ?',
            [$userId]
        );
        $customerName = $admin['name']  ?? $admin['username'] ?? 'Cliente';
        $email        = $admin['email'] ?? '';
        $hashId       = bin2hex(random_bytes(16));

        // Insertar en orders con el esquema real
        $this->db->execute(
            'INSERT INTO orders
                (customer_id, customer_name, email, phone, address, city, total, status, hash_id, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $customerName,
                $email,
                'N/A',          // phone
                '',             // address
                'Sin definir',  // city
                $total,
                'pending',
                $hashId,
                'Pedido desde CITROB Shop',
            ]
        );

        $orderId = (int)$this->db->lastInsertId();

        // Insertar items de la orden con el esquema real (product_name, qty, subtotal)
        foreach ($items as $item) {
            $subtotal = $item['unit_price'] * $item['qty'];
            $this->db->execute(
                'INSERT INTO order_items (order_id, product_id, product_name, qty, unit_price, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $orderId,
                    $item['product_id'],
                    $item['product_name'],
                    $item['qty'],
                    $item['unit_price'],
                    $subtotal,
                ]
            );
        }

        // Vaciar el carrito
        $this->db->execute('DELETE FROM cart_items WHERE cart_id = ?', [$cartId]);

        return new JsonModel(['success' => true, 'hash_id' => $hashId, 'order_id' => $orderId]);
    }
}
