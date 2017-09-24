<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Behavior;

use Kant\Foundation\Behavior;
use Kant\Controller\Controller;

/**
 * NoCsrf Behavior
 * It's used to disable Csrf Validation On Controller's Actions
 *
 * For example
 *
 * Add behavior function to Controller
 *
 ```
public function behaviors()
{
    return [
        'csrf' => [
            'class' => NoCsrf::className(),
            'controller' => $this,
                'actions' => [
                    'action-name'
                ]
            ]
    ];
}

```
 */
class NoCsrf extends Behavior
{

    public $actions = [];

    public $controller;

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeActions'
        ];
    }

    public function beforeAction($event)
    {
        $action = $event->action->id;
        if (in_array($action, $this->actions)) {
            $this->controller->enableCsrfValidation = false;
        }
    }
}
