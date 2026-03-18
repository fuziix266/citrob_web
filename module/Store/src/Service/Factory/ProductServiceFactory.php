<?php
declare(strict_types=1);

namespace Store\Service\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Store\Service\ProductService;
use StoreAdmin\Service\DbService;

class ProductServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): ProductService
    {
        return new ProductService($c->get(DbService::class));
    }
}
