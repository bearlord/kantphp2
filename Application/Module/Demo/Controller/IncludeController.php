<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Demo\Controller;
use Kant\Controller\BaseController;

/**
 * Description of IncludeController
 *
 * @author zhangzhenqiang
 */
class IncludeController extends BaseController {
    
    public function indexAction() {
        $FooModel = new \Demo\Model\FooModel();
        $FooModel->loop();
    }
}
