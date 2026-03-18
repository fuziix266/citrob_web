<?php
declare(strict_types=1);

namespace StoreAdmin\Service\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Service\AdminAuthService;
use StoreAdmin\Service\DbService;

class AdminAuthServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): AdminAuthService
    {
        return new AdminAuthService($container->get(DbService::class));
    }
}
