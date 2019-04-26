<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Action;

use Kant\Kant;
use Kant\Foundation\Component;

class Action extends Component
{

    /**
     *
     * @var string ID of the action
     */
    public $id;

    /**
     *
     * @var Controller|\Kant\Controller\Controller the controller that owns this action
     */
    public $controller;

    /**
     * Constructor.
     *
     * @param string $id
     *            the ID of this action
     * @param Controller $controller
     *            the controller that owns this action
     * @param array $config
     *            name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $config = [])
    {
        $this->id = $id;
        $this->controller = $controller;
        parent::__construct($config);
    }

    /**
     * Returns the unique ID of this action among the whole application.
     *
     * @return string the unique ID of this action among the whole application.
     */
    public function getUniqueId()
    {
        return $this->controller->getUniqueId();
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     *
     * @param array $params
     *            the parameters to be bound to the action's run() method.
     * @return mixed the result of the action
     * @throws InvalidConfigException if the action class does not have a run() method
     */
    public function runWithParams($params)
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }
        
        Kant::trace('Running action: ' . get_class($this) . '::run()', __METHOD__);
        
        if ($this->beforeRun()) {
            // $result = call_user_func_array([$this, 'run'], $params);
            $result = Kant::$container->call([
                $this,
                'run'
            ], $params);
            $this->afterRun();
            
            return $result;
        } else {
            return null;
        }
    }

    /**
     * This method is called right before `run()` is executed.
     * You may override this method to do preparation work for the action run.
     * If the method returns false, it will cancel the action.
     *
     * @return boolean whether to run the action.
     */
    protected function beforeRun()
    {
        return true;
    }

    /**
     * This method is called right after `run()` is executed.
     * You may override this method to do post-processing work for the action run.
     */
    protected function afterRun()
    {}
}
