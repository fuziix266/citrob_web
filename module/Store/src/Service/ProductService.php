<?php
declare(strict_types=1);

namespace Store\Service;

use StoreAdmin\Service\DbService;

class ProductService
{
    public function __construct(private DbService $db) {}

    public function getActive(): array
    {
        return $this->db->query(
            'SELECT p.*, c.slug AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.active=1 ORDER BY p.featured DESC, p.id ASC'
        );
    }

    public function getById(int $id): ?array
    {
        return $this->db->queryOne('SELECT * FROM products WHERE id=? AND active=1', [$id]);
    }
}
