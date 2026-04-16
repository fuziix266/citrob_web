<?php
declare(strict_types=1);

namespace StoreAdmin\Service;

use StoreAdmin\Service\DbService;

class AdminAuthService
{
    private const SESSION_KEY = 'citrob_admin_id';

    public function __construct(private DbService $db) {}

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        if (!headers_sent()) session_name('citrob_admin');
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function login(string $username, string $password): bool
    {
        $this->startSession();
        $admin = $this->db->queryOne(
            'SELECT * FROM admins WHERE username = ? AND active = 1 LIMIT 1',
            [$username]
        );
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $this->db->execute('UPDATE admins SET last_login = NOW() WHERE id = ?', [$admin['id']]);
            $_SESSION[self::SESSION_KEY] = $admin['id'];
            $_SESSION['admin_name']      = $admin['name'] ?? $admin['username'];
            $_SESSION['is_admin']        = (bool)$admin['is_admin'];
            return true;
        }
        return false;
    }

    public function logout(): void
    {
        $this->startSession();
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        $this->startSession();
        return isset($_SESSION[self::SESSION_KEY]) && (int)$_SESSION[self::SESSION_KEY] > 0;
    }

    public function isAdmin(): bool
    {
        $this->startSession();
        return $this->isLoggedIn() && !empty($_SESSION['is_admin']);
    }

    public function requireLogin(string $loginUrl): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    public function getCurrentAdmin(): ?array
    {
        if (!$this->isLoggedIn()) return null;
        return $this->db->queryOne(
            'SELECT id, username, name, email, is_admin FROM admins WHERE id = ? AND active = 1',
            [$_SESSION[self::SESSION_KEY]]
        );
    }
}
