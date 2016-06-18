<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

class ResponseController extends BaseController {

    public function indexAction() {
        \Kant\Http\Response::create("Hello World!", 200, [
            'content-type' => 'application/json'
        ])->send();
    }

}
