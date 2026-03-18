<?php

declare(strict_types=1);

namespace StoreAdmin;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;

class Module implements ConfigProviderInterface
{
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e): void
    {
        $app    = $e->getApplication();
        $events = $app->getEventManager();

        $events->attach(MvcEvent::EVENT_RENDER, function (MvcEvent $e) {
            $routeMatch = $e->getRouteMatch();
            if (!$routeMatch) return;

            $routeName = $routeMatch->getMatchedRouteName();

            // Rutas sin layout (tienen su propio HTML completo + setTerminal(true))
            $noLayoutRoutes = ['admin-login', 'admin-logout'];
            if (in_array($routeName, $noLayoutRoutes, true)) return;

            // Rutas que usan el layout del admin
            $adminRoutes = ['admin', 'admin-products', 'admin-categories', 'admin-orders', 'admin-api'];
            if (!in_array($routeName, $adminRoutes, true)) return;

            $layout = $e->getViewModel();
            if (!$layout instanceof ViewModel) return;

            $layout->setTemplate('store-admin/layout/layout');
        }, 100);
    }
}
