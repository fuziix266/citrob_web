<?php

declare(strict_types=1);

namespace Store;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'shop' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/shop',
                    'defaults' => [
                        'controller' => Controller\ShopController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'shop-api-products' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/shop/api/products',
                    'defaults' => [
                        'controller' => Controller\ShopController::class,
                        'action'     => 'apiProducts',
                    ],
                ],
            ],
            'shop-api-categories' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/shop/api/categories',
                    'defaults' => [
                        'controller' => Controller\ShopController::class,
                        'action'     => 'apiCategories',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ShopController::class => Controller\Factory\ShopControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\ProductService::class  => Service\Factory\ProductServiceFactory::class,
            Service\CategoryService::class => Service\Factory\CategoryServiceFactory::class,
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'store/layout/layout' => __DIR__ . '/../view/store/layout/layout.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
