<?php
declare(strict_types=1);

namespace StoreAdmin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StoreAdmin\Controller\ApiController;
use StoreAdmin\Service\{AdminAuthService, DbService};

class ApiControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $name, ?array $o = null): ApiController
    {
        return new ApiController($c->get(AdminAuthService::class), $c->get(DbService::class));
    }
}
