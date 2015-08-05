<?php

namespace Help\Controller;

use Kant\Controller\BaseController;

class IndexController extends BaseController {

    public function indexAction() {
        $this->view->display();
    }

}
