<?php

declare(strict_types=1);

namespace Store\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Store\Controller\CartController;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class CartControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): CartController
    {
        return new CartController(
            $c->get(AdminAuthService::class),
            $c->get(DbService::class)
        );
    }
}
