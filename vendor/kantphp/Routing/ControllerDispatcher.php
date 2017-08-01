<?php

namespace Kant\Routing;

use Kant\Kant;
use Kant\Support\Collection;
use Kant\Exception\InvalidRouteException;

class ControllerDispatcher {

    use RouteDependencyResolverTrait;

    /**
     * The container instance.
     *
     * @var \Kant\Container\Container
     */
    protected $container;

    /**
     * Create a new controller dispatcher instance.
     *
     * @param  \Kant\Container\Container  $container
     * @return void
     */
    public function __construct() {
        
    }

    /**
     * Dispatch a request to a given controller and method.
     *
     * @param  \Kant\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method) {
        $parameters = $this->resolveClassMethodDependencies(
                $route->parametersWithoutNulls(), $controller, $method
        );

        $controllerClassName = get_class($controller);
        $path = explode("\\", $controllerClassName);

        //module name
        $moduleName = ucfirst($path[1]);
        
        //controller name
        $controllerName = ucfirst(str_replace("Controller", "", $path[3]));
        
        //action name
        $actionName = str_replace("Action", "", $method);
        
        $_route = "$moduleName/$controllerName/$actionName";

        return $this->run($_route);
    }
    
    /**
     * Execution
     * 
     * @throws KantException
     * @throws ReflectionException
     */
    public function run($dispatcher) {        
        $data = $this->runAction($dispatcher);
        return $data;
    }

    /**
     * Runs a controller action specified by a route.
     * This method parses the specified route and creates the corresponding child module(s), controller and action
     * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
     * If the route is empty, the method will use [[defaultRoute]].
     * @param string $route the route that specifies the action.
     * @param array $params the parameters to be passed to the action
     * @return mixed the result of the action.
     * @throws InvalidRouteException if the requested route cannot be resolved into an action successfully.
     */
    public function runAction($route, $params = []) {
        $parts = Kant::$app->createController($route, 'explicit');

        if (is_array($parts)) {
            /* @var $controller \Kant\Controller\Controller */
            list($controller, $actionID) = $parts;  
            $controller->routePattern = 'explicit';
            $controller->view->dispatcher = $route;

            $oldController = Kant::$app->controller;

            Kant::$app->controller = $controller;
            $result = $controller->runActions($actionID, $params);

            if ($oldController !== null) {
                Kant::$app->controller = $oldController;
            }

            return $result;
        }

        throw new InvalidRouteException('Unable to resolve the request "' . $route . '".');
    }

    /**
     * Creates a controller instance based on the given route.
     *
     * The route should be relative to this module. The method implements the following algorithm
     * to resolve the given route:
     * 
     * If any of the above steps resolves into a controller, it is returned together with the rest
     * part of the route which will be treated as the action ID. Otherwise, false will be returned.
     *
     * @param string $route the route consisting of module, controller and action IDs.
     * @return array|boolean If the controller is created successfully, it will be returned together
     * with the requested action ID. Otherwise false will be returned.
     * @throws InvalidConfigException if the controller class and its file do not match.
     */
//    public function createController($route) {
//        // double slashes or leading/ending slashes may cause substr problem
//        $route = trim($route, '/');
//        if (strpos($route, '//') !== false) {
//            return false;
//        }
//
//        if (strpos($route, '/') !== false) {
//            $path = explode("/", $route);
//            if (count($path) !== 3) {
//                return false;
//            }
//            
//            $moduleName = explode("/", $route)[0];
//            Kant::$app->setModuleConfig($moduleName);
//            
//            $controller = $this->createControllerByID($route);
//            return $controller === null ? false : [$controller, end($path)];
//        }
//    }

    /**
     * Creates a controller based on the given controller ID.
     *
     * The controller ID is relative to this module. The controller class
     * should be namespaced under [[controllerNamespace]].
     *
     * Note that this method does not check [[modules]] or [[controllerMap]].
     *
     * @param string $id the controller ID
     * @return Controller the newly created controller instance, or null if the controller ID is invalid.
     * @throws InvalidConfigException if the controller class and its file name do not match.
     * This exception is only thrown when in debug mode.
     */
//    public function createControllerByID($id) {
//        if (strrpos($id, '/') === false) {
//            return null;
//        }
//
//        list($moduleName, $controllerName, $actionName) = explode("/", $id);
//
//        $className = sprintf("App\%s\RouteControllers\%sController", ucfirst($moduleName), ucfirst($controllerName));
//
//        if (strpos($className, '-') !== false || !class_exists($className)) {
//            return null;
//        }
//
//        if (is_subclass_of($className, 'Kant\Controller\Controller')) {
//            $controller = Kant::createObject($className, [$controllerName, $moduleName]);
//            return get_class($controller) === $className ? $controller : null;
//        } elseif (Kant::$app->config->get('debug')) {
//            throw new InvalidConfigException("Controller class must extend from \\Kant\\Controller\\Controller.");
//        } else {
//            return null;
//        }
//    }

    /**
     * Get the middleware for the controller instance.
     *
     * @param  \Kant\Routing\Controller  $controller
     * @param  string  $method
     * @return array
     */
    public static function getMiddleware($controller, $method) {
        if (!method_exists($controller, 'getMiddleware')) {
            return [];
        }

        return (new Collection($controller->getMiddleware()))->reject(function ($data) use ($method) {
                    return static::methodExcludedByOptions($method, $data['options']);
                })->pluck('middleware')->all();
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param  string  $method
     * @param  array  $options
     * @return bool
     */
    protected static function methodExcludedByOptions($method, array $options) {
        return (isset($options['only']) && !in_array($method, (array) $options['only'])) ||
                (!empty($options['except']) && in_array($method, (array) $options['except']));
    }

}
