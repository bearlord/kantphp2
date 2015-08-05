<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

class SqliteController extends BaseController {

    protected $dM;

    public function __construct() {
        $this->dM = $this->model("SqliteDemo");
    }

    public function IndexAction() {
        $a = $this->dM->readAll();
        var_dump($a);
        echo "index";
    }

}

?>
