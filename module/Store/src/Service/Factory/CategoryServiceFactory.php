<?php
declare(strict_types=1);

namespace Store\Service\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Store\Service\CategoryService;
use StoreAdmin\Service\DbService;

class CategoryServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): CategoryService
    {
        return new CategoryService($c->get(DbService::class));
    }
}
