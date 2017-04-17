<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Controller;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Controller\ActionEvent;
use Kant\Action\InlineAction;
use Kant\Exception\InvalidArgumentException;
use Kant\Exception\BadRequestHttpException;

/**
 * Base Controller 
 * 
 * @access public
 * @since version 1.0
 * @todo .etc
 */
class Controller extends Component {

    use \Kant\Traits\UrlTrait,
        \Kant\Traits\LangTrait,
        \Kant\Traits\WidgetTrait;

    /**
     * @event ActionEvent an event raised right before executing a controller action.
     * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';

    /**
     * @event ActionEvent an event raised right after executing a controller action.
     */
    const EVENT_AFTER_ACTION = 'afterAction';

    /**
     * @var string the ID of this controller.
     */
    public $id;

    /**
     * @var string the ID of the action that is used when the action ID is not specified
     * in the request. Defaults to 'index'.
     */
    public $defaultAction = 'index';

    /**
     * The Action suffix
     * @var type 
     */
    public $actionSuffix = 'Action';

    /**
     *
     * @var type 
     */
    protected $view;

    /**
     *
     * @var type 
     */
    protected $dispatcher;

    /**
     * Layout
     */
    public $layout = 'main';

    /**
     * @var Action the action that is currently being executed. This property will be set
     * by [[run()]] when it is called by [[Application]] to run an action.
     */
    public $action;
    
    /**
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[\yii\web\Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = true;

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
        $this->initView();
    }

    public function actions() {
        return [];
    }

    /**
     * initialize
     */
    public function init() {

    }

    /**
     * initView
     * 
     * @return type
     */
    protected function initView() {
        $this->view = Kant::$app->getView();
        $this->view->layout = $this->layout;
        return $this->view;
    }

    /**
     * Runs a request specified in terms of a route.
     * 
     */
    public function run() {
        
    }

    /**
     * Runs an action within this controller with the specified action ID and parameters.
     * If the action ID is empty, the method will use [[defaultAction]].
     * @param string $id the ID of the action to be executed.
     * @return mixed the result of the action.
     * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
     * @see createAction()
     */
    public function runAction($id, $params = []) {
        $action = $this->createAction($id);
        if ($action === null) {
            throw new InvalidArgumentException('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
        }

        $oldAction = $this->action;
        $this->action = $action;

        $result = null;

        if ($this->beforeAction($action)) {
            // run the action
            $result = $action->runWithParams($params);
            $result = $this->afterAction($action, $result);
        }

        $this->action = $oldAction;

        return $result;
    }

    /**
     * Creates an action based on the given action ID.
     * The method first checks if the action ID has been declared in [[actions()]]. If so,
     * it will use the configuration declared there to create the action object.
     * If not, it will look for a controller method whose name is in the format of `actionXyz`
     * where `Xyz` stands for the action ID. If found, an [[InlineAction]] representing that
     * method will be created and returned.
     * @param string $id the action ID.
     * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
     */
    public function createAction($id) {
        if ($id === '') {
            $id = $this->defaultAction;
        }

        $actionMap = $this->actions();

        if (isset($actionMap[$id])) {
            return Kant::createObject($actionMap[$id], [$id, $this]);
        } elseif (preg_match('/^[\w+\\-]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $methodName = $this->formatMethodName($id);
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return new InlineAction($id, $this, $methodName);
                }
            }
            return Kant::$container->call([$this, $methodName]);
        }

        return null;
    }

    /**
     * Retrun the formatted method name
     * 
     * @param string $id
     * @return type
     */
    protected function formatMethodName($id) {
        if (strpos($id, $this->actionSuffix) > 1) {
            return $id;
        }
        return str_replace(' ', '', strtolower(implode(' ', explode('-', $id)))) . $this->actionSuffix;
    }

    /**
     * This method is invoked right before an action is executed.
     *
     * The method will trigger the [[EVENT_BEFORE_ACTION]] event. The return value of the method
     * will determine whether the action should continue to run.
     *
     * In case the action should not run, the request should be handled inside of the `beforeAction` code
     * by either providing the necessary output or redirecting the request. Otherwise the response will be empty.
     *
     * If you override this method, your code should look like the following:
     *
     * ```php
     * public function beforeAction($action)
     * {
     *     // your custom code here, if you want the code to run before action filters,
     *     // which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl
     *
     *     if (!parent::beforeAction($action)) {
     *         return false;
     *     }
     *
     *     // other custom code here
     *
     *     return true; // or false to not run the action
     * }
     * ```
     *
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to run.
     */
    public function beforeAction($action) {
        $event = new ActionEvent($action);
        $this->trigger(self::EVENT_BEFORE_ACTION, $event);
//        return $event->isValid;
        if ($event->isValid) {
            if ($this->enableCsrfValidation && !Kant::$app->getRequest()->validateCsrfToken()) {
                throw new BadRequestHttpException(Kant::t('kant', 'Unable to verify your data submission.'));
            }
            return true;
        }
    }

    /**
     * This method is invoked right after an action is executed.
     *
     * The method will trigger the [[EVENT_AFTER_ACTION]] event. The return value of the method
     * will be used as the action return value.
     *
     * If you override this method, your code should look like the following:
     *
     * ```php
     * public function afterAction($action, $result)
     * {
     *     $result = parent::afterAction($action, $result);
     *     // your custom code here
     *     return $result;
     * }
     * ```
     *
     * @param Action $action the action just executed.
     * @param mixed $result the action return result.
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result) {
        $event = new ActionEvent($action);
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_ACTION, $event);
        return $event->result;
    }

    /**
     * Returns the unique ID of the controller.
     * @return string the controller ID that is prefixed with the module ID (if any).
     */
    public function getUniqueId() {
        return $this->id;
    }

}
