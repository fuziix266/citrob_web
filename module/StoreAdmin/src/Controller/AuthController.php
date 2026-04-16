<?php
declare(strict_types=1);

namespace StoreAdmin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class AuthController extends AbstractActionController
{
    public function __construct(
        private AdminAuthService $auth,
        private DbService $db
    ) {}

    public function loginAction(): ViewModel|\Laminas\Http\Response
    {
        if ($this->auth->isLoggedIn()) {
            return $this->auth->isAdmin() ? $this->redirect()->toRoute('admin') : $this->redirect()->toRoute('shop');
        }

        $error = null;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $username = trim((string)($data['username'] ?? ''));
            $password = (string)($data['password'] ?? '');

            if ($this->auth->login($username, $password)) {
                return $this->auth->isAdmin() ? $this->redirect()->toRoute('admin') : $this->redirect()->toRoute('shop');
            }
            $error = 'Usuario o contraseña incorrectos.';
        }

        $vm = new ViewModel(['error' => $error]);
        $vm->setTemplate('store-admin/auth/login');
        $vm->setTerminal(true); // La vista contiene el HTML completo, no necesita layout wrapper
        return $vm;
    }

    public function logoutAction(): \Laminas\Http\Response
    {
        $this->auth->logout();
        return $this->redirect()->toRoute('shop');
    }

    public function registerAction(): ViewModel|\Laminas\Http\Response
    {
        if ($this->auth->isLoggedIn()) {
            return $this->auth->isAdmin() ? $this->redirect()->toRoute('admin') : $this->redirect()->toRoute('shop');
        }

        $error = null;
        $success = null;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $username = trim((string)($data['username'] ?? ''));
            $password = (string)($data['password'] ?? '');
            $password_confirm = (string)($data['password_confirm'] ?? '');

            if (empty($username) || empty($password) || empty($password_confirm)) {
                $error = 'Por favor, completa todos los campos.';
            } elseif ($password !== $password_confirm) {
                $error = 'Las contraseñas no coinciden.';
            } else {
                // Verificar si existe
                $existing = $this->db->queryOne('SELECT id FROM admins WHERE username = ?', [$username]);
                if ($existing) {
                    $error = 'El nombre de usuario ya está en uso.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $this->db->execute(
                        'INSERT INTO admins (username, password_hash, active, created_at) VALUES (?, ?, 1, NOW())',
                        [$username, $hashed]
                    );
                    
                    if ($this->auth->login($username, $password)) {
                        return $this->redirect()->toRoute('shop');
                    }
                }
            }
        }

        $vm = new ViewModel(['error' => $error, 'success' => $success]);
        $vm->setTemplate('store-admin/auth/register');
        $vm->setTerminal(true);
        return $vm;
    }
}
