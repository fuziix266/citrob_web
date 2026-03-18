<?php
declare(strict_types=1);

namespace StoreAdmin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use StoreAdmin\Service\AdminAuthService;

class AuthController extends AbstractActionController
{
    public function __construct(private AdminAuthService $auth) {}

    public function loginAction(): ViewModel|\Laminas\Http\Response
    {
        if ($this->auth->isLoggedIn()) {
            return $this->redirect()->toRoute('admin');
        }

        $error = null;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $username = trim((string)($data['username'] ?? ''));
            $password = (string)($data['password'] ?? '');

            if ($this->auth->login($username, $password)) {
                return $this->redirect()->toRoute('admin');
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
        return $this->redirect()->toRoute('admin-login');
    }
}
