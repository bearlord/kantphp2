<?php

namespace App\Index\Controller;

use Kant\Controller\Controller;
use Kant\Http\Request;

class ValidateController extends Controller {

    public function behaviors() {
        return [
            'csrf' => [
                'class' => \Kant\Behavior\NoCsrf::className(),
                'controller' => $this,
                'actions' => [
                    'index'
                ]
            ]
        ];
    }

    public function indexAction(Request $request) {
//        if ($request->isMethod('post')) {
//            var_dump($request->validateCsrfToken());
//        }
        $model = \App\Index\Model\Posts::find()->one();
        $this->view->model = $model;
        return $this->view->render();
    }

}
