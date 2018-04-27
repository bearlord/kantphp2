<?php

namespace Kant\Routing;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Foundation\Pipeline;
use Kant\Routing\RouteCollection;
use Kant\Routing\RouteGroup;
use Closure;
use Kant\Support\Collection;
use Kant\Helper\StringHelper;
use Kant\Http\Request;
use Kant\Http\Response;

class Router extends Component
{

	protected $mapFileExt = ".php";

	/**
	 * The route collection instance.
	 *
	 * @var \Kant\Routing\RouteCollection
	 */
	public $routes;

	/**
	 * The attributes to pass on to the router.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * The currently dispatched route instance.
	 *
	 * @var \Kant\Routing\Route
	 */
	protected $current;

	/**
	 * The globally available parameter patterns.
	 *
	 * @var array
	 */
	protected $patterns = [];

	/**
	 * The route group attribute stack.
	 *
	 * @var array
	 */
	protected $groupStack = [];

	/**
	 * All of the verbs supported by the router.
	 *
	 * @var array
	 */
	public static $verbs = [
		'GET',
		'HEAD',
		'POST',
		'PUT',
		'PATCH',
		'DELETE',
		'OPTIONS'
	];

	/**
	 * The request currently being dispatched.
	 *
	 * @var \Kant\Http\Request
	 */
	protected $currentRequest;

	/**
	 * All of the short-hand keys for middlewares.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * All of the middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [];

	/**
	 * The priority-sorted list of middleware.
	 *
	 * Forces the listed middleware to always be in the given order.
	 *
	 * @var array
	 */
	public $middlewarePriority = [];

	public function __construct()
	{
		$this->routes = Kant::createObject(RouteCollection::class);
		$this->mapRoutes();
	}

	/**
	 * Define routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	public function mapRoutes()
	{
		foreach (glob(CFG_PATH . "Route/*.php") as $map) {
			$mapName = StringHelper::basename($map, $this->mapFileExt);
			if (strtolower($mapName) === 'route') {
				$this->group([
					'middleware' => null
						], $map);
				continue;
			}
			$this->group([
				'prefix' => strtolower($mapName),
				'middleware' => ($mapName),
				'namespace' => "App\\{$mapName}\\RouteControllers"
					], $map);
		}
	}

	/**
	 * Get the Route Collection object.
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Register a new GET route with the router.
	 *
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function get($uri, $action = null)
	{
		return $this->addRoute([
					'GET',
					'HEAD'
						], $uri, $action);
	}

	/**
	 * Register a new POST route with the router.
	 *
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function post($uri, $action = null)
	{
		return $this->addRoute('POST', $uri, $action);
	}

	/**
	 * Register a new PUT route with the router.
	 *
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function put($uri, $action = null)
	{
		return $this->addRoute('PUT', $uri, $action);
	}

	/**
	 * Register a new PATCH route with the router.
	 *
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function patch($uri, $action = null)
	{
		return $this->addRoute('PATCH', $uri, $action);
	}

	/**
	 * Register a new DELETE route with the router.
	 *
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function delete($uri, $action = null)
	{
		return $this->addRoute('DELETE', $uri, $action);
	}

	/**
	 * Register a new OPTIONS route with the router.
	 *
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function options($uri, $action = null)
	{
		return $this->addRoute('OPTIONS', $uri, $action);
	}

	/**
	 * Register a new route responding to all verbs.
	 *
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function any($uri, $action = null)
	{
		$verbs = [
			'GET',
			'HEAD',
			'POST',
			'PUT',
			'PATCH',
			'DELETE'
		];

		return $this->addRoute($verbs, $uri, $action);
	}

	/**
	 * Register a new route with the given verbs.
	 *
	 * @param array|string $methods            
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	public function match($methods, $uri, $action = null)
	{
		return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
	}

	/**
	 * Register an array of resource controllers.
	 *
	 * @param array $resources            
	 * @return void
	 */
	public function resources(array $resources)
	{
		foreach ($resources as $name => $controller) {
			$this->resource($name, $controller);
		}
	}

	/**
	 * Route a resource to a controller.
	 *
	 * @param string $name            
	 * @param string $controller            
	 * @param array $options            
	 * @return void
	 */
	public function resource($name, $controller, array $options = [])
	{
		$registrar = new ResourceRegistrar($this);

		$registrar->register($name, $controller, $options);
	}

	/**
	 * Create a route group with shared attributes.
	 *
	 * @param array $attributes            
	 * @param \Closure|string $routes            
	 * @return void
	 */
	public function group(array $attributes, $routes)
	{
		$this->updateGroupStack($attributes);

		// Once we have updated the group stack, we'll load the provided routes and
		// merge in the group's attributes when the routes are created. After we
		// have created the routes, we will pop the attributes off the stack.
		$this->loadRoutes($routes);
		array_pop($this->groupStack);
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param array $attributes            
	 * @return void
	 */
	protected function updateGroupStack(array $attributes)
	{
		if (!empty($this->groupStack)) {
			$attributes = RouteGroup::merge($attributes, end($this->groupStack));
		}

		$this->groupStack[] = $attributes;
	}

	/**
	 * Merge the given array with the last group stack.
	 *
	 * @param array $new            
	 * @return array
	 */
	public function mergeWithLastGroup($new)
	{
		return RouteGroup::merge($new, end($this->groupStack));
	}

	/**
	 * Load the provided routes.
	 *
	 * @param \Closure|string $routes            
	 * @return void
	 */
	protected function loadRoutes($routes)
	{
		if ($routes instanceof Closure) {
			$routes($this);
		} else {
			$router = $this;
			require $routes;
		}
	}

	/**
	 * Add a route to the underlying route collection.
	 *
	 * @param array|string $methods            
	 * @param string $uri            
	 * @param \Closure|array|string|null $action            
	 * @return \Kant\Routing\Route
	 */
	protected function addRoute($methods, $uri, $action)
	{
		return $this->routes->add($this->createRoute($methods, $uri, $action));
	}

	/**
	 * Create a new route instance.
	 *
	 * @param array|string $methods            
	 * @param string $uri            
	 * @param mixed $action            
	 * @return \Kant\Routing\Route
	 */
	protected function createRoute($methods, $uri, $action)
	{
		// If the route is routing to a controller we will parse the route action into
		// an acceptable array format before registering it and creating this route
		// instance itself. We need to build the Closure that will call this out.
		if ($this->actionReferencesController($action)) {
			$action = $this->convertToControllerAction($action);
		}

		$route = $this->newRoute($methods, $this->prefix($uri), $action);

		// If we have groups that need to be merged, we will merge them now after this
		// route has already been created and is ready to go. After we're done with
		// the merge we will be ready to return the route back out to the caller.
		if ($this->hasGroupStack()) {
			$this->mergeGroupAttributesIntoRoute($route);
		}

		$this->addWhereClausesToRoute($route);

		return $route;
	}

	/**
	 * Determine if the action is routing to a controller.
	 *
	 * @param array $action            
	 * @return bool
	 */
	protected function actionReferencesController($action)
	{
		if (!$action instanceof Closure) {
			return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
		}

		return false;
	}

	/**
	 * Add a controller based route action to the action array.
	 *
	 * @param array|string $action            
	 * @return array
	 */
	protected function convertToControllerAction($action)
	{
		if (is_string($action)) {
			$action = [
				'uses' => $action
			];
		}

		// Here we'll merge any group "uses" statement if necessary so that the action
		// has the proper clause for this property. Then we can simply set the name
		// of the controller on the action and return the action array for usage.
		if (!empty($this->groupStack)) {
			$action['uses'] = $this->prependGroupNamespace($action['uses']);
		}

		// Here we will set this controller name on the action array just so we always
		// have a copy of it for reference if we need it. This can be used while we
		// search for a controller name or do some other type of fetch operation.
		$action['controller'] = $action['uses'];

		return $action;
	}

	/**
	 * Prepend the last group namespace onto the use clause.
	 *
	 * @param string $class            
	 * @return string
	 */
	protected function prependGroupNamespace($class)
	{
		$group = end($this->groupStack);

		return isset($group['namespace']) && strpos($class, '\\') !== 0 ? $group['namespace'] . '\\' . $class : $class;
	}

	/**
	 * Create a new Route object.
	 *
	 * @param array|string $methods            
	 * @param string $uri            
	 * @param mixed $action            
	 * @return \Kant\Routing\Route
	 */
	protected function newRoute($methods, $uri, $action)
	{
		return (new Route($methods, $uri, $action))->setRouter($this);
	}

	/**
	 * Prefix the given URI with the last prefix.
	 *
	 * @param string $uri            
	 * @return string
	 */
	protected function prefix($uri)
	{
		return trim(trim($this->getLastGroupPrefix(), '/') . '/' . trim($uri, '/'), '/') ?: '/';
	}

	/**
	 * Get the prefix from the last group on the stack.
	 *
	 * @return string
	 */
	public function getLastGroupPrefix()
	{
		if (!empty($this->groupStack)) {
			$last = end($this->groupStack);

			return isset($last['prefix']) ? $last['prefix'] : '';
		}

		return '';
	}

	/**
	 * Add the necessary where clauses to the route based on its initial registration.
	 *
	 * @param \Kant\Routing\Route $route            
	 * @return \Kant\Routing\Route
	 */
	protected function addWhereClausesToRoute($route)
	{
		$route->where(array_merge($this->patterns, isset($route->getAction()['where']) ? $route->getAction()['where'] : []));

		return $route;
	}

	/**
	 * Merge the group stack with the controller action.
	 *
	 * @param \Kant\Routing\Route $route            
	 * @return void
	 */
	protected function mergeGroupAttributesIntoRoute($route)
	{
		$route->setAction($this->mergeWithLastGroup($route->getAction()));
	}

	/**
	 * Dispatch the request to the application.
	 *
	 * @param \Kant\Http\Request $request            
	 * @return \Kant\Http\Response
	 */
	public function dispatch(Request $request, Response $response)
	{
		$this->currentRequest = $request;

		return $this->dispatchToRoute($request, $response);
	}

	/**
	 * Dispatch the request to a route and return the response.
	 *
	 * @param \Kant\Http\Request $request            
	 * @return mixed
	 */
	public function dispatchToRoute(Request $request, Response $response)
	{
		// First we will find a route that matches this request. We will also set the
		// route resolver on the request so middlewares assigned to the route will
		// receive access to this route instance for checking of the parameters.
		$route = $this->findRoute($request);

		if (!$route) {
			return $this->dispatchToModule($request, $response);
		}

		$request->setRouteResolver(function () use($route) {
			return $route;
		});

		return $this->runRouteWithinStack($route, $request, $response);
	}

	/**
	 * Find the route matching a given request.
	 *
	 * @param \Kant\Http\Request $request            
	 * @return \Kant\Routing\Route
	 */
	protected function findRoute($request)
	{
		$this->current = $route = $this->routes->match($request);

		return $route;
	}

	/**
	 * Run the given route within a Stack "onion" instance.
	 *
	 * @param \Kant\Routing\Route $route            
	 * @param \Kant\Http\Response $response            
	 * @return mixed
	 */
	protected function runRouteWithinStack(Route $route, Request $request, Response $response)
	{
		$middleware = $this->gatherRouteMiddleware($route);
//		var_dump($middleware);
		
		return (new Pipeline(Kant::$container))
						->send($request)
//						->through($middleware)
						->then(function($request) use ($route, $response) {
							$response->setContent($route->run());
							return $response;
						});
	}

	/**
	 * Gather the middleware for the given route with resolved class names.
	 *
	 * @param \Kant\Routing\Route $route            
	 * @return array
	 */
	public function gatherRouteMiddleware(Route $route)
	{
		$middleware = (new Collection($route->gatherMiddleware()))->map(function ($name) {
					return (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups);
				})->flatten();

		return $this->sortMiddleware($middleware);
	}

	/**
	 * Sort the given middleware by priority.
	 *
	 * @param \Kant\Support\Collection $middlewares            
	 * @return array
	 */
	protected function sortMiddleware(Collection $middlewares)
	{
		return (new SortedMiddleware($this->middlewarePriority, $middlewares))->all();
	}

	/**
	 * Determine if the router currently has a group stack.
	 *
	 * @return bool
	 */
	public function hasGroupStack()
	{
		return !empty($this->groupStack);
	}

	/**
	 * Dispatch the request to a module and return the response.
	 *
	 * @param \Kant\Http\Request $request            
	 * @return mixed
	 */
	public function dispatchToModule(Request $request, Response $response)
	{
		$response->setContent((new ModuleDispatcher())->dispatch($request));
		return $response;
	}

	/**
	 * Register a short-hand name for a middleware.
	 *
	 * @param string $name            
	 * @param string $class            
	 * @return $this
	 */
	public function aliasMiddleware($name, $class)
	{
		$this->middleware[$name] = $class;

		return $this;
	}

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param string $method            
	 * @param array $args            
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	public static function __callStatic($method, $args)
	{
		$instance = self;
		return call_user_func_array([
			$instance,
			$method
				], $args);
	}

}
