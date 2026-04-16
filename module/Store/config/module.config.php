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
            'shop-api-cart' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/shop/api/cart[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\CartController::class,
                        'action'     => 'get',
                    ],
                ],
            ],
            'shop-orden' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/orden/:hash',
                    'defaults' => [
                        'controller' => Controller\OrderPublicController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ShopController::class => Controller\Factory\ShopControllerFactory::class,
            Controller\CartController::class => Controller\Factory\CartControllerFactory::class,
            Controller\OrderPublicController::class => Controller\Factory\OrderPublicControllerFactory::class,
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
            'store/order/public'  => __DIR__ . '/../view/store/order/public.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
