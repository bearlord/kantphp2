<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Foundation;

use Kant\Kant;
use Kant\Di\ServiceLocator;
use Kant\Exception\InvalidParamException;

/**
 * Module is the base class for module and application classes.
 *
 * A module represents a sub-application which contains MVC elements by itself, such as
 * models, views, controllers, etc.
 *
 * A module may consist of [[modules|sub-modules]].
 *
 * [[components|Components]] may be registered with the module so that they are globally
 * accessible within the module.
 *
 * @property array $aliases List of path aliases to be defined. The array keys are alias names (must start
 *           with '@') and the array values are the corresponding paths or aliases. See [[setAliases()]] for an example.
 *           This property is write-only.
 * @property string $basePath The root directory of the module.
 * @property string $controllerPath The directory that contains the controller classes. This property is
 *           read-only.
 * @property string $layoutPath The root directory of layout files. Defaults to "[[viewPath]]/layouts".
 * @property array $modules The modules (indexed by their IDs).
 * @property string $uniqueId The unique ID of the module. This property is read-only.
 * @property string $viewPath The root directory of view files. Defaults to "[[basePath]]/views".
 *          
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Module extends ServiceLocator
{

    /**
     *
     * @var array mapping from controller ID to controller configurations.
     *      Each name-value pair specifies the configuration of a single controller.
     *      A controller configuration can be either a string or an array.
     *      If the former, the string should be the fully qualified class name of the controller.
     *      If the latter, the array must contain a 'class' element which specifies
     *      the controller's fully qualified class name, and the rest of the name-value pairs
     *      in the array are used to initialize the corresponding controller properties. For example,
     *     
     *      ```php
     *      [
     *      'account' => 'app\controllers\UserController',
     *      'article' => [
     *      'class' => 'app\controllers\PostController',
     *      'pageTitle' => 'something new',
     *      ],
     *      ]
     *      ```
     */
    public $controllerMap = [];

    /**
     *
     * @var string the default route of this module. Defaults to 'default'.
     *      The route may consist of child module ID, controller ID, and/or action ID.
     *      For example, `help`, `post/create`, `admin/post/create`.
     *      If action ID is not given, it will take the default value as specified in
     *      [[Controller::defaultAction]].
     */
    public $defaultRoute = 'default';

    /**
     *
     * @var string the root directory of the module.
     */
    private $_basePath;
    private $_vendorPath;

    /*
     * @var string the implicit controller template
     */
    public $implicitControllerClassTpl = "App\%s\Controllers\%sController";

    /**
     *
     * @var string the explicit controoler template
     */
    public $explicitControllerClassTpl = "App\%s\Controllers\%sController";

    /**
     * Returns the root directory of the module.
     * It defaults to the directory containing the module class file.
     * 
     * @return string the root directory of the module.
     */
    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $class = new \ReflectionClass($this);
            $this->_basePath = dirname($class->getFileName());
        }
        return $this->_basePath;
    }
	
	    /**
     * Sets the root directory of the module.
     * This method can only be invoked at the beginning of the constructor.
     * @param string $path the root directory of the module. This can be either a directory name or a [path alias](guide:concept-aliases).
     * @throws InvalidParamException if the directory does not exist.
     */
    public function setBasePath($path)
    {
        $path = Kant::getAlias($path);
        $p = strncmp($path, 'phar://', 7) === 0 ? $path : realpath($path);
        if ($p !== false && is_dir($p)) {
            $this->_basePath = $p;
        } else {
            throw new InvalidParamException("The directory does not exist: $path");
        }
    }

    /**
     * Returns the directory that stores vendor files.
     * 
     * @return string the directory that stores vendor files.
     *         Defaults to "vendor" directory under [[basePath]].
     */
    public function getVendorPath()
    {
        if ($this->_vendorPath === null) {
            $this->setVendorPath(dirname($this->getBasePath()) . '/vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     * 
     * @param string $path
     *            the directory that stores vendor files.
     */
    public function setVendorPath($path)
    {
        $this->_vendorPath = Kant::getAlias($path);
        Kant::setAlias('@vendor', $this->_vendorPath);
        Kant::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower');
    }

    /**
     * Runs a controller action specified by a route.
     * This method parses the specified route and creates the corresponding child module(s), controller and action
     * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
     * If the route is empty, the method will use [[defaultRoute]].
     * 
     * @param string $route
     *            the route that specifies the action.
     * @param array $params
     *            the parameters to be passed to the action
     * @return mixed the result of the action.
     * @throws InvalidRouteException if the requested route cannot be resolved into an action successfully.
     */
    public function runAction($route, $params = [])
    {
        $parts = $this->createController($route);

        if (is_array($parts)) {
            /* @var $controller \Kant\Controller\Controller */
            list ($controller, $actionID) = $parts;
            $controller->routePattern = 'implicit';
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
     * @param string $route
     *            the route consisting of module, controller and action IDs.
     * @param string $pattern
     *            the route pattern
     * @param string $layout
     *            the layout
     * @return array|boolean If the controller is created successfully, it will be returned together
     *         with the requested action ID. Otherwise false will be returned.
     * @throws InvalidConfigException if the controller class and its file do not match.
     */
    public function createController($route, $pattern = 'implicit')
    {
        // double slashes or leading/ending slashes may cause substr problem
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }

        if (strpos($route, '/') !== false) {
            $path = explode("/", $route);
            if (count($path) !== 3) {
                return false;
            }

            $moduleName = explode("/", $route)[0];

            $controller = $this->createControllerByID($route, $pattern);

            return $controller === null ? false : [
                $controller,
                end($path)
            ];
        }
    }

    /**
     * Creates a controller based on the given controller ID.
     *
     * The controller ID is relative to this module. The controller class
     * should be namespaced under [[controllerNamespace]].
     *
     * Note that this method does not check [[modules]] or [[controllerMap]].
     *
     * @param string $id
     *            the controller ID
     * @return Controller the newly created controller instance, or null if the controller ID is invalid.
     * @throws InvalidConfigException if the controller class and its file name do not match.
     *         This exception is only thrown when in debug mode.
     */
    public function createControllerByID($id, $pattern = 'implicit')
    {
        if (strrpos($id, '/') === false) {
            return null;
        }
        list ($moduleName, $controllerName, $actionName) = explode("/", strtolower($id));
        
        if (strpos($controllerName, "-") !== false) {
            $controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $controllerName)));
        }

        if ($pattern == 'implicit') {
            $className = sprintf("app\module\%s\Controllers\%sController", $moduleName, ucfirst($controllerName));
        } else {
            $className = sprintf("app\module\%s\RouteControllers\%sController", $moduleName, ucfirst($controllerName));
        }

        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }

        if (is_subclass_of($className, 'Kant\Controller\Controller')) {
            $controller = Kant::createObject($className, [
                        $controllerName,
                        $moduleName
            ]);
            return get_class($controller) === $className ? $controller : null;
        } elseif (Kant::$app->config->get('debug')) {
            throw new InvalidConfigException("Controller class must extend from \\Kant\\Controller\\Controller.");
        } else {
            return null;
        }
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|string $callback            
     * @param array $parameters            
     * @param string|null $defaultMethod            
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        if ($this->isCallableWithAtSign($callback) || $defaultMethod) {
            return $this->callClass($callback, $parameters, $defaultMethod);
        }
        $dependencies = $this->getMethodDependencies($callback, $parameters);
        return call_user_func_array($callback, $dependencies);
    }

    /**
     * Determine if the given string is in Class@method syntax.
     *
     * @param mixed $callback            
     * @return bool
     */
    protected function isCallableWithAtSign($callback)
    {
        return is_string($callback) && strpos($callback, '@') !== false;
    }

    /**
     * Get all dependencies for a given method.
     *
     * @param callable|string $callback            
     * @param array $parameters            
     * @return array
     */
    protected function getMethodDependencies($callback, array $parameters = [])
    {
        $dependencies = [];

        foreach ($this->getCallReflector($callback)->getParameters() as $parameter) {
            $this->addDependencyForCallParameter($parameter, $parameters, $dependencies);
        }
        return array_merge($parameters, $dependencies);
        // return array_merge($dependencies, $parameters);
    }

    /**
     * Get the proper reflection instance for the given callback.
     *
     * @param callable|string $callback            
     * @return \ReflectionFunctionAbstract
     */
    protected function getCallReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }
        if (is_array($callback)) {
            list ($class, $name) = $callback;
            if (!preg_match('/^[A-Za-z](\w)*$/', $name)) {
                throw new ReflectionException('Method not provided.');
            }
            $method = new ReflectionMethod($class, $name);
            if (!$method->isPublic()) {
                throw new ReflectionException('Method not provided');
            }
            return $method;
        }

        return new ReflectionFunction($callback);
    }

    /**
     * Get the dependency for the given call parameter.
     *
     * @param \ReflectionParameter $parameter            
     * @param array $parameters            
     * @param array $dependencies            
     * @return mixed
     */
    protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies)
    {
        if (array_key_exists($parameter->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->name];

            unset($parameters[$parameter->name]);
        } elseif ($parameter->getClass()) {
            $dependencies[] = $this->make($parameter->getClass()->name);
        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        }
    }

    /**
     * Call a string reference to a class using Class@method syntax.
     *
     * @param string $target            
     * @param array $parameters            
     * @param string|null $defaultMethod            
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function callClass($target, array $parameters = [], $defaultMethod = null)
    {
        $segments = explode('@', $target);

        // If the listener has an @ sign, we will assume it is being used to delimit
        // the class name from the handle method name. This allows for handlers
        // to run multiple handler methods in a single class for convenience.
        $method = count($segments) == 2 ? $segments[1] : $defaultMethod;

        if (is_null($method)) {
            throw new InvalidArgumentException('Method not provided.');
        }
        return $this->call([
                    $this->make($segments[0]),
                    $method
                        ], $parameters);

        // return $this->call([Kant::$container->get($segments[0]), $method], $parameters);
    }

    /**
     * Resolve the given type from the container.
     */
    public function make($class)
    {
        return Kant::createObject($class);
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param string $method            
     * @param array $options            
     * @return bool
     */
    protected static function methodExcludedByOptions($method, array $options)
    {
        return (isset($options['only']) && !in_array($method, (array) $options['only'])) || (!empty($options['except']) && in_array($method, (array) $options['except']));
    }

}
