<?php

declare(strict_types=1);

namespace Store\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Store\Controller\OrderPublicController;
use StoreAdmin\Service\DbService;

class OrderPublicControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): OrderPublicController
    {
        return new OrderPublicController($c->get(DbService::class));
    }
}
