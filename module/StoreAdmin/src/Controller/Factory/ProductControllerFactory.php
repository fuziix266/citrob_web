<?php
declare(strict_types=1);

namespace StoreAdmin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Controller\ProductController;
use StoreAdmin\Service\{AdminAuthService, DbService};

class ProductControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): ProductController
    {
        return new ProductController($c->get(AdminAuthService::class), $c->get(DbService::class));
    }
}
