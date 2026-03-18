<?php

declare(strict_types=1);

namespace StoreAdmin\Service;

use PDO;
use RuntimeException;

class DbService
{
    private PDO $pdo;

    public function __construct(array $config = [])
    {
        // ENV vars (Coolify/Docploy) tienen prioridad sobre config array local
        $host    = getenv('DB_HOST')     ?: ($config['host']       ?? 'localhost');
        $port    = getenv('DB_PORT')     ?: ($config['port']       ?? '3306');
        $dbname  = getenv('DB_DATABASE') ?: ($config['dbname']     ?? 'citrobbd');
        $user    = getenv('DB_USERNAME') ?: ($config['username']   ?? 'root');
        $pass    = getenv('DB_PASSWORD') ?: ($config['password']   ?? '');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $this->pdo = new PDO(
                $dsn, $user, $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (\PDOException $e) {
            throw new RuntimeException('DB connection failed: ' . $e->getMessage());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
