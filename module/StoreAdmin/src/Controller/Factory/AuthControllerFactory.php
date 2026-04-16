<?php
declare(strict_types=1);

namespace StoreAdmin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Controller\AuthController;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): AuthController
    {
        return new AuthController(
            $c->get(AdminAuthService::class),
            $c->get(DbService::class)
        );
    }
}
