<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Action;

use Kant\Kant;

/**
 * InlineAction represents an action that is defined as a controller method.
 *
 * The name of the controller method is available via [[actionMethod]] which
 * is set by the [[controller]] who creates this action.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{

    /**
     *
     * @var string the controller method that this inline action is associated with
     */
    public $actionMethod;

    /**
     *
     * @param string $id
     *            the ID of this action
     * @param Controller $controller
     *            the controller that owns this action
     * @param string $actionMethod
     *            the controller method that this inline action is associated with
     * @param array $config
     *            name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $actionMethod, $config = [])
    {
        $this->actionMethod = $actionMethod;
        parent::__construct($id, $controller, $config);
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     * 
     * @param array $params
     *            action parameters
     * @return mixed the result of the action
     */
    public function runWithParams($params)
    {
        Kant::trace('Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()', __METHOD__);
        return Kant::$container->call([
            $this->controller,
            $this->actionMethod
        ], $params);
    }
}
