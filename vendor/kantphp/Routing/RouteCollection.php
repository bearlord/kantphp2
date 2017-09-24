<?php
namespace Kant\Routing;

use Kant\Routing\Route;
use Kant\Http\Request;
use Kant\Support\Arr;
// use Kant\Exception\NotFoundHttpException;
use Kant\Exception\MethodNotAllowedHttpException;

class RouteCollection
{

    /**
     * An array of the routes keyed by method.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = [];

    /**
     * A look-up table of routes by their names.
     *
     * @var array
     */
    protected $nameList = [];

    /**
     * A look-up table of routes by controller action.
     *
     * @var array
     */
    protected $actionList = [];

    public function __construct()
    {
        $this->time = microtime(true);
    }

    /**
     * Add a Route instance to the collection.
     *
     * @param \Kant\Routing\Route $route            
     * @return \Kant\Routing\Route
     */
    public function add(Route $route)
    {
        $this->addToCollections($route);
        
        $this->addLookups($route);
        
        return $route;
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param \Kant\Routing\Route $route            
     * @return void
     */
    protected function addToCollections($route)
    {
        $domainAndUri = $route->domain() . $route->uri();
        
        foreach ($route->methods() as $method) {
            $this->routes[$method][$domainAndUri] = $route;
        }
        
        $this->allRoutes[$method . $domainAndUri] = $route;
    }

    /**
     * Add the route to any look-up tables if necessary.
     *
     * @param \Kant\Routing\Route $route            
     * @return void
     */
    protected function addLookups($route)
    {
        // If the route has a name, we will add it to the name look-up table so that we
        // will quickly be able to find any route associate with a name and not have
        // to iterate through every route every time we need to perform a look-up.
        $action = $route->getAction();
        
        if (isset($action['as'])) {
            $this->nameList[$action['as']] = $route;
        }
        
        // When the route is routing to a controller we will also store the action that
        // is used by the route. This will let us reverse route to controllers while
        // processing a request and easily generate URLs to the given controllers.
        if (isset($action['controller'])) {
            $this->addToActionList($action, $route);
        }
    }

    /**
     * Add a route to the controller action dictionary.
     *
     * @param array $action            
     * @param \Kant\Routing\Route $route            
     * @return void
     */
    protected function addToActionList($action, $route)
    {
        $this->actionList[trim($action['controller'], '\\')] = $route;
    }

    /**
     * Find the first route matching a given request.
     *
     * @param \Kant\Http\Request $request            
     * @return \Kant\Routing\Route
     *
     * @throws \Kant\Exception\NotFoundHttpException
     */
    public function match(Request $request)
    {
        $routes = $this->get($request->getMethod());
        
        // First, we will see if we can find a matching route for this current request
        // method. If we can, great, we can just return it so that it can be called
        // by the consumer. Otherwise we will check for routes with another verb.
        $route = $this->matchAgainstRoutes($routes, $request);
        
        if (! is_null($route)) {
            return $route->bind($request);
        }
        
        // If no route was found we will now check if a matching route is specified by
        // another HTTP verb. If it is we will need to throw a MethodNotAllowed and
        // inform the user agent of which HTTP verb it should use for this route.
        $others = $this->checkForAlternateVerbs($request);
        
        if (count($others) > 0) {
            return $this->getRouteForMethods($request, $others);
        }
        
        // throw new NotFoundHttpException;
    }

    /**
     * Determine if a route in the array matches the request.
     *
     * @param array $routes            
     * @param \Kant\http\Request $request            
     * @param bool $includingMethod            
     * @return \Kant\Routing\Route|null
     */
    protected function matchAgainstRoutes(array $routes, $request, $includingMethod = true)
    {
        return Arr::first($routes, function ($value) use($request, $includingMethod) {
            return $value->matches($request, $includingMethod);
        });
    }

    /**
     * Determine if any routes match on another HTTP verb.
     *
     * @param \Kant\Http\Request $request            
     * @return array
     */
    protected function checkForAlternateVerbs($request)
    {
        $methods = array_diff(Router::$verbs, [
            $request->getMethod()
        ]);
        
        // Here we will spin through all verbs except for the current request verb and
        // check to see if any routes respond to them. If they do, we will return a
        // proper error response with the correct headers on the response string.
        $others = [];
        
        foreach ($methods as $method) {
            if (! is_null($this->matchAgainstRoutes($this->get($method), $request, false))) {
                $others[] = $method;
            }
        }
        
        return $others;
    }

    /**
     * Get a route (if necessary) that responds when other available methods are present.
     *
     * @param \Kant\Http\Request $request            
     * @param array $methods            
     * @return \Kant\Routing\Route
     *
     * @throws \Kant\Exception\MethodNotAllowedHttpException
     */
    protected function getRouteForMethods($request, array $methods)
    {
        if ($request->method() == 'OPTIONS') {
            return (new Route('OPTIONS', $request->path(), function () use($methods) {
                return new Response('', 200, [
                    'Allow' => implode(',', $methods)
                ]);
            }))->bind($request);
        }
        $this->methodNotAllowed($methods);
    }

    /**
     * Throw a method not allowed HTTP exception.
     *
     * @param array $others            
     * @return void
     *
     * @throws \Kant\Exception\MethodNotAllowedHttpException
     */
    protected function methodNotAllowed(array $others)
    {
        throw new MethodNotAllowedHttpException($others);
    }

    /**
     * Get routes from the collection by method.
     *
     * @param string|null $method            
     * @return array
     */
    public function get($method = null)
    {
        return is_null($method) ? $this->getRoutes() : Arr::get($this->routes, $method, []);
    }

    /**
     * Get a route instance by its name.
     *
     * @param string $name            
     * @return \Kant\Routing\Route|null
     */
    public function getByName($name)
    {
        return isset($this->nameList[$name]) ? $this->nameList[$name] : null;
    }

    /**
     * Get a route instance by its controller action.
     *
     * @param string $action            
     * @return \Kant\Routing\Route|null
     */
    public function getByAction($action)
    {
        return isset($this->actionList[$action]) ? $this->actionList[$action] : null;
    }

    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes()
    {
        return array_values($this->allRoutes);
    }

    /**
     * Get all of the routes keyed by their HTTP verb / method.
     *
     * @return array
     */
    public function getRoutesByMethod()
    {
        return $this->routes;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getRoutes());
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->getRoutes());
    }
}
