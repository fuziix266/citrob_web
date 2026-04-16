<?php
declare(strict_types=1);

namespace Store\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Store\Controller\ShopController;
use Store\Service\{ProductService, CategoryService};
use StoreAdmin\Service\AdminAuthService;

class ShopControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): ShopController
    {
        return new ShopController(
            $c->get(ProductService::class), 
            $c->get(CategoryService::class),
            $c->get(AdminAuthService::class)
        );
    }
}
