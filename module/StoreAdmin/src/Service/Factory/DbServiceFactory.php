<?php
declare(strict_types=1);

namespace StoreAdmin\Service\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Service\DbService;

class DbServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): DbService
    {
        $config = $container->get('config')['db'] ?? [];
        return new DbService($config);
    }
}
