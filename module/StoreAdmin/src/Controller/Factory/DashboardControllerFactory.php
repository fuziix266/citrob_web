<?php
declare(strict_types=1);

namespace StoreAdmin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Controller\DashboardController;
use StoreAdmin\Service\{AdminAuthService, DbService};

class DashboardControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): DashboardController
    {
        return new DashboardController($c->get(AdminAuthService::class), $c->get(DbService::class));
    }
}
