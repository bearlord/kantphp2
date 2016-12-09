<?php

namespace App\Index\Controller;

use Kant\Controller\Controller;

class IndexController extends Controller {

    public function indexAction() {
        $config = \Kant\KantFactory::getConfig()->get("token");
        var_dump($config);
        $this->view->display();
    }
    
    public function modelAction() {
        $PostsModel = new \App\Index\Model\PostsModel();
        $PostsModel->a = "100";
        echo $PostsModel->a;
    }
}
