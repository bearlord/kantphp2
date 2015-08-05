<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

/**
 * \Demo\IndexController
 */
class IndexController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 欢迎
     */
    public function indexAction() {
        echo "Welcome to KantPHP Framework";
    }

    /**
     * 赋值到视图
     */
    public function displayAction() {
        $this->view->str = 'hello';
        $this->view->row = array('0ne' => 'Tom', 'Two' => '中文');
        $this->view->display();
    }

    /**
     * 视图函数
     */
    public function displayfuncAction() {
        $this->view->time = time();
        $this->view->str = "abcdefg";
        $this->view->display();
    }

    /**
     * Get
     */
    public function getAction() {
        print_r($_GET);
        print_r($this->get);
        $a = $this->input->get('id', 'intval', '99');
        echo $a;
    }

    /**
     * Post 
     */
    public function postAction() {
        var_dump($_POST);
        var_dump($this->post);
    }

    public function sessionAction() {
        $_SESSION['hello'] = 'hello world';
        echo session_id();
        var_dump($_SESSION);
    }

    public function runtimeAction() {
        Runtime::mark('end');
        $usage = Runtime::calculate();
        var_dump($usage);
        $fun = get_defined_functions();
        print_r($fun['user']);
    }

    public function _empty() {
        echo 'empty';
    }

}

?>
