<?php
declare(strict_types=1);

namespace Web\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction(): ViewModel
    {
        $vm = new ViewModel();
        $vm->setTemplate('web/index/index');
        return $vm;
    }
}
