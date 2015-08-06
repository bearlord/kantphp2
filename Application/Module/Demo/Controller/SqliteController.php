<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

class SqliteController extends BaseController {

    protected $model;

    public function __construct() {
        $this->model = $this->model("SqliteDemo");
    }

    public function IndexAction() {
        $a = $this->model->readAll();
        var_dump($a);
        echo "index";
    }

}

?>
