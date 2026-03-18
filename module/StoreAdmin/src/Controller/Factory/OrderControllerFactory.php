<?php
declare(strict_types=1);

namespace StoreAdmin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Controller\OrderController;
use StoreAdmin\Service\{AdminAuthService, DbService};

class OrderControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): OrderController
    {
        return new OrderController($c->get(AdminAuthService::class), $c->get(DbService::class));
    }
}
