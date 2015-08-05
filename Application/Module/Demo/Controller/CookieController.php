<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

class CookieController extends BaseController {

    public function indexAction() {
        $this->setAction();
    }

    public function setAction() {
        $this->cookie->set('str', "Hello World!!");
        $this->cookie->set('array', array("name" => '张三', 'sex' => '男'));
        var_dump($_COOKIE);
    }

    public function getAction() {
        $str = $this->cookie->get('str');
        $array = $this->cookie->get("array");
        var_dump($str);
        var_dump($array);
//		var_dump($_COOKIE);
        highlight_file(__FILE__);
    }

}

?>
