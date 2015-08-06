<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

class SqliteController extends BaseController {

    protected $model;

    public function __construct() {
        $this->model = $this->model("SqliteDemo");
    }

    public function IndexAction() {
        $row = $this->model->readAll();
        var_dump($row);
        echo "index";
    }

}

?>
