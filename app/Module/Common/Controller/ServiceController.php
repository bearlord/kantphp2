<?php

namespace App\Common\Controller;

use Kant\Controller\Controller;

class ServiceController extends Controller {

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'captcha' => [
                'class' => 'Kant\Captcha\CaptchaAction',
                'fixedVerifyCode' => null,
            ],
        ];
    }

}
