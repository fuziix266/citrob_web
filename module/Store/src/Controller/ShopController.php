<?php
declare(strict_types=1);

namespace Store\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Store\Service\ProductService;
use Store\Service\CategoryService;

class ShopController extends AbstractActionController
{
    public function __construct(
        private ProductService $products,
        private CategoryService $categories
    ) {}

    public function indexAction(): ViewModel
    {
        $products   = $this->products->getActive();
        $categories = $this->categories->getActiveWithCount();

        $vm = new ViewModel([
            'products'   => json_encode($products, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
            'categories' => json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
        ]);
        $vm->setTemplate('store/shop/index');
        return $vm;
    }

    public function apiProductsAction(): JsonModel
    {
        $products = $this->products->getActive();
        return new JsonModel(['success' => true, 'data' => $products]);
    }

    public function apiCategoriesAction(): JsonModel
    {
        $cats = $this->categories->getActiveWithCount();
        return new JsonModel(['success' => true, 'data' => $cats]);
    }
}
