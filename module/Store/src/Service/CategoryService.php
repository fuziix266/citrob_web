<?php
declare(strict_types=1);

namespace Store\Service;

use StoreAdmin\Service\DbService;

class CategoryService
{
    public function __construct(private DbService $db) {}

    public function getActiveWithCount(): array
    {
        $cats = $this->db->query(
            'SELECT c.*, COUNT(p.id) AS count FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.active=1 WHERE c.active=1 GROUP BY c.id ORDER BY c.sort_order ASC'
        );
        // Recalculate "todos" with total count
        $total = array_reduce($cats, fn($carry, $c) => $c['slug'] !== 'todos' ? $carry + (int)$c['count'] : $carry, 0);
        return array_map(fn($c) => $c['slug'] === 'todos' ? array_merge($c, ['count' => $total]) : $c, $cats);
    }
}
