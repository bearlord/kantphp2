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
use Kant\Exception\InvalidConfigException;

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
	 * @var array custom module parameters (name => value).
	 */
	public $params = [];

	/**
	 * @var string an ID that uniquely identifies this module among other modules which have the same [[module|parent]].
	 */
	public $id;

	/**
	 * @var Module the parent module of this module. `null` if this module does not have a parent.
	 */
	public $module;

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
	 *      'account' => 'app\Controller\UserController',
	 *      'article' => [
	 *      'class' => 'app\Controller\PostController',
	 *      'pageTitle' => 'something new',
	 *      ],
	 *      ]
	 *      ```
	 */
	public $controllerMap = [];
	
	/**
     * @var string the namespace that controller classes are in.
     * This namespace will be used to load controller classes by prepending it to the controller
     * class name.
     *
     * If not set, it will use the `controllers` sub-namespace under the namespace of this module.
     * For example, if the namespace of this module is `foo\bar`, then the default
     * controller namespace would be `foo\bar\controllers`.
     *
     * See also the [guide section on autoloading](guide:concept-autoloading) to learn more about
     * defining namespaces and how classes are loaded.
     */
    public $controllerNamespace = "\\app\\modules";

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

	/**
	 * @var string the root directory that contains view files for this module
	 */
	private $_viewPath;

	/**
	 * @var string the root directory that contains layout view files for this module.
	 */
	private $_layoutPath;

	/**
	 * Constructor.
	 * @param string $id the ID of this module.
	 * @param Module $parent the parent module (if any).
	 * @param array $config name-value pairs that will be used to initialize the object properties.
	 */
	public function __construct($id, $parent = null, $config = [])
	{
		$this->id = $id;
		$this->module = $parent;
		parent::__construct($config);
	}

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
	 * Returns the directory that contains the view files for this module.
	 * @return string the root directory of view files. Defaults to "[[basePath]]/views".
	 */
	public function getViewPath()
	{
		if ($this->_viewPath === null) {
			$this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'view';
		}
		return $this->_viewPath;
	}

	/**
	 * Sets the directory that contains the view files.
	 * @param string $path the root directory of view files.
	 * @throws InvalidParamException if the directory is invalid.
	 */
	public function setViewPath($path)
	{
		$this->_viewPath = Kant::getAlias($path);
	}

	/**
	 * Returns the directory that contains layout view files for this module.
	 * @return string the root directory of layout files. Defaults to "[[viewPath]]/layouts".
	 */
	public function getLayoutPath()
	{
		if ($this->_layoutPath === null) {
			$this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
		}

		return $this->_layoutPath;
	}

	/**
	 * Sets the directory that contains the layout files.
	 * @param string $path the root directory or [path alias](guide:concept-aliases) of layout files.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setLayoutPath($path)
	{
		$this->_layoutPath = Kant::getAlias($path);
	}

	/**
	 * Defines path aliases.
	 * This method calls [[Kant::setAlias()]] to register the path aliases.
	 * This method is provided so that you can define path aliases when configuring a module.
	 * @property array list of path aliases to be defined. The array keys are alias names
	 * (must start with `@`) and the array values are the corresponding paths or aliases.
	 * See [[setAliases()]] for an example.
	 * @param array $aliases list of path aliases to be defined. The array keys are alias names
	 * (must start with `@`) and the array values are the corresponding paths or aliases.
	 * For example,
	 *
	 * ```php
	 * [
	 *     '@models' => '@app/models', // an existing alias
	 *     '@backend' => __DIR__ . '/../backend',  // a directory
	 * ]
	 * ```
	 */
	public function setAliases($aliases)
	{
		foreach ($aliases as $name => $alias) {
			Kant::setAlias($name, $alias);
		}
	}

	/**
	 * Checks whether the child module of the specified ID exists.
	 * This method supports checking the existence of both child and grand child modules.
	 * @param string $id module ID. For grand child modules, use ID path relative to this module (e.g. `admin/content`).
	 * @return bool whether the named module exists. Both loaded and unloaded modules
	 * are considered.
	 */
	public function hasModule($id)
	{
		if (($pos = strpos($id, '/')) !== false) {
			// sub-module
			$module = $this->getModule(substr($id, 0, $pos));

			return $module === null ? false : $module->hasModule(substr($id, $pos + 1));
		}
		return isset($this->_modules[$id]);
	}

	/**
	 * Retrieves the child module of the specified ID.
	 * This method supports retrieving both child modules and grand child modules.
	 * @param string $id module ID (case-sensitive). To retrieve grand child modules,
	 * use ID path relative to this module (e.g. `admin/content`).
	 * @param bool $load whether to load the module if it is not yet loaded.
	 * @return Module|null the module instance, `null` if the module does not exist.
	 * @see hasModule()
	 */
	public function getModule($id, $load = true)
	{
		if (($pos = strpos($id, '/')) !== false) {
			// sub-module
			$module = $this->getModule(substr($id, 0, $pos));

			return $module === null ? null : $module->getModule(substr($id, $pos + 1), $load);
		}

		if (isset($this->_modules[$id])) {
			if ($this->_modules[$id] instanceof Module) {
				return $this->_modules[$id];
			} elseif ($load) {
				Kant::trace("Loading module: $id", __METHOD__);
				/* @var $module Module */
				$module = Kant::createObject($this->_modules[$id], [$id, $this]);
				$module->setInstance($module);
				return $this->_modules[$id] = $module;
			}
		}

		return null;
	}

	/**
	 * Adds a sub-module to this module.
	 * @param string $id module ID.
	 * @param Module|array|null $module the sub-module to be added to this module. This can
	 * be one of the following:
	 *
	 * - a [[Module]] object
	 * - a configuration array: when [[getModule()]] is called initially, the array
	 *   will be used to instantiate the sub-module
	 * - `null`: the named sub-module will be removed from this module
	 */
	public function setModule($id, $module)
	{
		if ($module === null) {
			unset($this->_modules[$id]);
		} else {
			$this->_modules[$id] = $module;
		}
	}

	/**
	 * Returns the sub-modules in this module.
	 * @param bool $loadedOnly whether to return the loaded sub-modules only. If this is set `false`,
	 * then all sub-modules registered in this module will be returned, whether they are loaded or not.
	 * Loaded modules will be returned as objects, while unloaded modules as configuration arrays.
	 * @return array the modules (indexed by their IDs).
	 */
	public function getModules($loadedOnly = false)
	{
		if ($loadedOnly) {
			$modules = [];
			foreach ($this->_modules as $module) {
				if ($module instanceof Module) {
					$modules[] = $module;
				}
			}

			return $modules;
		}
		return $this->_modules;
	}

	/**
	 * Registers sub-modules in the current module.
	 *
	 * Each sub-module should be specified as a name-value pair, where
	 * name refers to the ID of the module and value the module or a configuration
	 * array that can be used to create the module. In the latter case, [[Kant::createObject()]]
	 * will be used to create the module.
	 *
	 * If a new sub-module has the same ID as an existing one, the existing one will be overwritten silently.
	 *
	 * The following is an example for registering two sub-modules:
	 *
	 * ```php
	 * [
	 *     'comment' => [
	 *         'class' => 'app\modules\comment\CommentModule',
	 *         'db' => 'db',
	 *     ],
	 *     'booking' => ['class' => 'app\modules\booking\BookingModule'],
	 * ]
	 * ```
	 *
	 * @param array $modules modules (id => module configuration or instances).
	 */
	public function setModules($modules)
	{
		foreach ($modules as $id => $module) {
			$this->_modules[$id] = $module;
		}
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
	 * @param string $layout
	 *            the layout
	 * @return array|boolean If the controller is created successfully, it will be returned together
	 *         with the requested action ID. Otherwise false will be returned.
	 * @throws InvalidConfigException if the controller class and its file do not match.
	 */
	public function createController($route)
	{		
		// double slashes or leading/ending slashes may cause substr problem
		$route = trim($route, '/');
		if (strpos($route, '//') !== false) {
			return false;
		}

		if (strpos($route, '/') !== false) {
			list ($id, $route) = explode('/', $route, 2);
		} else {
            $id = $route;
            $route = '';
        }

		// module and controller map take precedence
		if (isset($this->controllerMap[$id])) {
			$controller = Kant::createObject($this->controllerMap[$id], [$id, $this]);
			return [$controller, $route];
		}
		
		if (($pos = strrpos($route, '/')) !== false) {
            $id .= '/' . substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        }
		

		$controller = $this->createControllerByID($id);
        if ($controller === null && $route !== '') {
            $controller = $this->createControllerByID($id . '/' . $route);
            $route = '';
        }
		
        return $controller === null ? false : [$controller, $route];
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
	public function createControllerByID($id)
	{
		$pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return null;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return null;
        }

		$className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
		
        $className = ltrim($this->controllerNamespace . '\\' . str_replace('/', '\\', $prefix . 'controllers\\')  . $className, '\\');
		
		if (strpos($className, '-') !== false || !class_exists($className)) {
			return null;
		}
		
		if (is_subclass_of($className, \Kant\Controller\Controller::className())) {
			$controller = Kant::createObject($className, [
						$id,
						$this
			]);
			//if route pattern is explicit, can not be accessed directly by routine
			if (!empty($controller) && $controller->routePattern === \Kant\Controller\Controller::ROUTE_PATTERN_EXPLICIT) {
				if (KANT_DEBUG) {
					throw new InvalidParamException("Explicit Route Pattern cannot be accessed directly: $route.");
				}
			}

			return get_class($controller) === $className ? $controller : null;
		} elseif (KANT_DEBUG) {
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
	}

	/**
	 * Resolve the given type from the container.
	 */
	public function make($class)
	{
		return Kant::createObject($class);
	}

}
