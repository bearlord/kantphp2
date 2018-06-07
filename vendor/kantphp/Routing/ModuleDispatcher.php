<?php

namespace Kant\Routing;

use Kant\Kant;
use Kant\Http\Request;
use Kant\Registry\KantRegistry;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionException;
use Kant\Exception\Exception;
use Kant\Exception\InvalidRouteException;

class ModuleDispatcher
{

	use RouteDependencyResolverTrait;

	/**
	 *
	 * The Controller suffix
	 * 
	 * @var type
	 */
	public $controllerSuffix = "Controller";

    /**
     * The default route
     *
     * @var array
     */
	public $defaultRoute = [
        'module' =>  'index',
        'controller' => 'index',
        'action' => 'index'
    ];

	/**
	 * Create a new controller dispatcher instance.
	 *
	 * @param \Kant\Container\Container $container            
	 * @return void
	 */
	public function __construct()
	{
		
	}

	/**
	 * Dispatch a request to a given controller and method.
	 *
	 * @param \Kant\Routing\Route $route            
	 * @param mixed $controller            
	 * @param string $method            
	 * @return mixed
	 */
	public function dispatch(Request $request)
	{
		$dispatcher = $this->parseUrl($request->path());
		return $this->run($dispatcher['route']);
	}

	/**
	 * Parse url to module parament
	 * 
	 * @param type $url            
	 */
	public function parseUrl($url)
	{
		$result = $this->parseRoute(strtolower($url));
		return $result;
	}

	/**
	 * Parse route
	 *
	 * @param type $pathinfo            
	 */
	protected function parseRoute($pathinfo)
	{
		$route = [
			null,
			null,
			null
		];
		$var = [];
		$pathinfo = trim($pathinfo, "/");
		// Special pathinof as demo/index/get/a,100/b,101?c=102&d=103
		if (strpos($pathinfo, "?") !== false) {
			$parse = explode("?", $pathinfo);
			$path = explode('/', $parse[0]);
			if (!empty($parse[1])) {
				parse_str($parse[1], $query);
				foreach ($query as $key => $val) {
					$dispatcher[$key] = urldecode($val);
				}
			}
		} else {
			// Normal pathinfo as demo/index/get/a,100/b,101
			$path = explode('/', $pathinfo);
		}

		$module = array_shift($path);
		$module = !empty($module) ? $module : $this->defaultRoute['module'];
		$controller = !empty($path) ? array_shift($path) : $this->defaultRoute['controller'];
		$action = !empty($path) ? array_shift($path) : $this->defaultRoute['action'];
		
		$route = sprintf("%s/%s/%s", $module, $controller, $action);
		return [
			'route' => $route,
			'var' => $var
		];
	}

	/**
	 * Execution
	 *
	 * @throws KantException
	 * @throws ReflectionException
	 */
	public function run($dispatcher)
	{
		$data = $this->runAction($dispatcher);
		return $data;
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
		$parts = Kant::$app->createController($route);

		if (is_array($parts)) {
			/* @var $controller \Kant\Controller\Controller */
			list ($controller, $actionID) = $parts;

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

}
