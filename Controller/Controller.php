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
use Kant\View\View;
use Kant\Action\ActionEvent;
use Kant\Action\InlineAction;
use Kant\Exception\InvalidArgumentException;
use Kant\Exception\BadRequestHttpException;

/**
 * Base Controller
 *
 * @property \Kant\View\View $view The view application component that is used to render various view files. This property is read-only.
 *          
 */
class Controller extends Component
{

    /**
     * @event ActionEvent an event raised right before executing a controller action.
     * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
     */
    const EVENT_BEFORE_ACTION = 'beforeActions';

    /**
     * @event ActionEvent an event raised right after executing a controller action.
     */
    const EVENT_AFTER_ACTION = 'afterActions';

    /**
     * Route Pattern explicit
     */
    const ROUTE_PATTERN_EXPLICIT = 'explicit';

    /**
     * Route Pattern implicit
     */
    const ROUTE_PATTERN_IMPLICIT = 'implicit';

    /**
     *
     * @var string the ID of this controller.
     */
    public $id;

    /**
     * @var Module the module that this controller belongs to.
     */
    public $module;

    /**
     *
     * @var string the ID of the action that is used when the action ID is not specified
     *      in the request. Defaults to 'index'.
     */
    public $defaultAction = 'index';

    public $actionSuffix = 'Action';

    /**
     *
     * @var type
     */
    public $view;

    /**
     * @var null|string|false the name of the layout to be applied to this controller's views.
     * This property mainly affects the behavior of [[render()]].
     * Defaults to null, meaning the actual layout value should inherit that from [[module]]'s layout value.
     * If false, no layout will be applied.
     */
    public $layout = 'main';

    /**
     *
     * @var \Kant\Action\Action the action that is currently being executed. This property will be set
     *      by [[run()]] when it is called by [[Application]] to run an action.
     */
    public $action;

    /**
     *
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     *      CSRF validation is enabled only when both this property and [[\Kant\Http\Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = true;

    /**
     *
     * @var explicit|implicit $routerPattern
     */
    public $routePattern;

    /**
     *
     * @var $dispatcher
     */
    public $dispatcher;

    /**
     * @param string $id the ID of this controller.
     * @param Module $module the module that this controller belongs to.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     */
    public function __construct($id = "", $module = "", $config = [])
    {
        $this->id = $id;
        $this->module = $module;
        parent::__construct($config);
    }

    public function actions()
    {
        return [];
    }

    /**
     * initialize
     */
    public function init()
    {
        $this->view = Kant::$app->getView();
        $this->view->layout = $this->layout;
    }

    /**
     * Runs a request specified in terms of a route.
     */
    public function run()
    {

    }

    /**
     * Runs an action within this controller with the specified action ID and parameters.
     * If the action ID is empty, the method will use [[defaultAction]].
     * 
     * @param string $id
     *            the ID of the action to be executed.
     * @return mixed the result of the action.
     * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
     * @see createAction()
     */
    public function runActions($id, $params = [])
    {
        $action = $this->createActions($id);
        if ($action === null) {
            throw new InvalidArgumentException('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
        }
        
        $oldAction = $this->action;
        $this->action = $action;
        
        $result = null;
        if ($this->beforeActions($action)) {
            // run the action
            $result = $action->runWithParams($params);
            $result = $this->afterActions($action, $result);
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
     * 
     * @param string $id
     *            the action ID.
     * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
     */
    public function createActions($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }
        
        $actionMap = $this->actions();
        
        if (isset($actionMap[$id])) {
            return Kant::createObject($actionMap[$id], [
                $id,
                $this
            ]);
        } elseif (preg_match('/^[\w+\\-]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $methodName = $this->formatMethodName($id);
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return new InlineAction($id, $this, $methodName);
                }
            }
            return Kant::$container->call([
                $this,
                $methodName
            ]);
        }
        
        return null;
    }

    /**
     * Retrun the formatted method name
     *
     * @param string $id            
     * @return type
     */
    protected function formatMethodName($id)
    {
        if (strpos($id, $this->actionSuffix) > 1) {
            return $id;
        }
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $id)))) . $this->actionSuffix;
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
     * public function beforeActions($action)
     * {
     * // your custom code here, if you want the code to run before action filters,
     * // which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl
     *
     * if (!parent::beforeAction($action)) {
     * return false;
     * }
     *
     * // other custom code here
     *
     * return true; // or false to not run the action
     * }
     * ```
     *
     * @param Action $action
     *            the action to be executed.
     * @return boolean whether the action should continue to run.
     */
    public function beforeActions($action)
    {
        $event = new ActionEvent($action);
        $this->trigger(self::EVENT_BEFORE_ACTION, $event);
        // return $event->isValid;
        if ($event->isValid) {
            if ($this->enableCsrfValidation && ! Kant::$app->getRequest()->validateCsrfToken()) {
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
     * public function afterActions($action, $result)
     * {
     * $result = parent::afterAction($action, $result);
     * // your custom code here
     * return $result;
     * }
     * ```
     *
     * @param Action $action
     *            the action just executed.
     * @param mixed $result
     *            the action return result.
     * @return mixed the processed action result.
     */
    public function afterActions($action, $result)
    {
        $event = new ActionEvent($action);
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_ACTION, $event);
        return $event->result;
    }

    /**
     * Returns the unique ID of the controller.
     * 
     * @return string the controller ID that is prefixed with the module ID (if any).
     */
    public function getUniqueId()
    {
        return $this->id;
    }

    /**
     * Redirects the browser to the specified URL.
     *
     * @param string|array $url
     *            the URL to be redirected to. This can be in one of the following formats:
     *            
     *            - a string representing a URL (e.g. "http://example.com")
     *            - a string representing a URL alias (e.g. "@example.com")
     *            - an array in the format of `[$route, ...name-value pairs...]` (e.g. `['site/index', 'ref' => 1]`)
     *            [[Url::to()]] will be used to convert the array into a URL.
     *            
     *            Any relative URL will be converted into an absolute one by prepending it with the host info
     *            of the current request.
     *            
     * @return Object Kant\Http\RedirectResponse
     */
    public function redirect($url)
    {
        return Kant::$app->redirect->to($url)
            ->withCookie(Kant::$app->response->headers->getCookies())
            ->send();
    }

    public function setIdOptions($options)
    {
        foreach ([
            'id',
            'module',
            'routePattern'
        ] as $value) {
            if (! empty($options[$value])) {
                $this->$value = $options[$value];
            }
        }
    }
}
