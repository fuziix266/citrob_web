<?php
declare(strict_types=1);

namespace StoreAdmin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Controller\CategoryController;
use StoreAdmin\Service\{AdminAuthService, DbService};

class CategoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): CategoryController
    {
        return new CategoryController($c->get(AdminAuthService::class), $c->get(DbService::class));
    }
}
