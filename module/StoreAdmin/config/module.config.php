<?php

declare(strict_types=1);

namespace StoreAdmin;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            // Login
            'admin-login' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/admin/login',
                    'defaults' => ['controller' => Controller\AuthController::class, 'action' => 'login'],
                ],
            ],
            'admin-logout' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/admin/logout',
                    'defaults' => ['controller' => Controller\AuthController::class, 'action' => 'logout'],
                ],
            ],
            // Dashboard
            'admin' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/admin',
                    'defaults' => ['controller' => Controller\DashboardController::class, 'action' => 'index'],
                ],
            ],
            // Products
            'admin-products' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin/products[/:action[/:id]]',
                    'defaults' => ['controller' => Controller\ProductController::class, 'action' => 'index'],
                    'constraints' => ['id' => '\d+', 'action' => '[a-zA-Z][a-zA-Z0-9_-]*'],
                ],
            ],
            // Categories
            'admin-categories' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin/categories[/:action[/:id]]',
                    'defaults' => ['controller' => Controller\CategoryController::class, 'action' => 'index'],
                    'constraints' => ['id' => '\d+', 'action' => '[a-zA-Z][a-zA-Z0-9_-]*'],
                ],
            ],
            // Orders
            'admin-orders' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin/orders[/:action[/:id]]',
                    'defaults' => ['controller' => Controller\OrderController::class, 'action' => 'index'],
                    'constraints' => ['id' => '\d+', 'action' => '[a-zA-Z][a-zA-Z0-9_-]*'],
                ],
            ],
            // API
            'admin-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin/api/:resource[/:action[/:id]]',
                    'defaults' => ['controller' => Controller\ApiController::class, 'action' => 'index'],
                    'constraints' => ['resource' => '[a-z]+', 'id' => '\d+', 'action' => '[a-zA-Z]+'],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AuthController::class      => Controller\Factory\AuthControllerFactory::class,
            Controller\DashboardController::class => Controller\Factory\DashboardControllerFactory::class,
            Controller\ProductController::class   => Controller\Factory\ProductControllerFactory::class,
            Controller\CategoryController::class  => Controller\Factory\CategoryControllerFactory::class,
            Controller\OrderController::class     => Controller\Factory\OrderControllerFactory::class,
            Controller\ApiController::class       => Controller\Factory\ApiControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\AdminAuthService::class => Service\Factory\AdminAuthServiceFactory::class,
            Service\DbService::class        => Service\Factory\DbServiceFactory::class,
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'store-admin/layout/layout'    => __DIR__ . '/../view/store-admin/layout/layout.phtml',
            'store-admin/auth/login'       => __DIR__ . '/../view/store-admin/auth/login.phtml',
            'store-admin/dashboard/index'  => __DIR__ . '/../view/store-admin/dashboard/index.phtml',
            'store-admin/product/index'    => __DIR__ . '/../view/store-admin/product/index.phtml',
            'store-admin/product/edit'     => __DIR__ . '/../view/store-admin/product/edit.phtml',
            'store-admin/category/index'   => __DIR__ . '/../view/store-admin/category/index.phtml',
            'store-admin/order/index'      => __DIR__ . '/../view/store-admin/order/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
