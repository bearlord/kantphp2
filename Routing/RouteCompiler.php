<?php
namespace Kant\Routing;

use Kant\Routing\Symfony\Route as SymfonyRoute;

class RouteCompiler
{

    /**
     * The route instance.
     *
     * @var \Kant\Routing\Route
     */
    protected $route;

    /**
     * Create a new Route compiler instance.
     *
     * @param \Kant\Routing\Route $route            
     * @return void
     */
    public function __construct($route)
    {
        $this->route = $route;
    }

    /**
     * Compile the route.
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function compile()
    {
        $optionals = $this->getOptionalParameters();
        
        $uri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->route->uri());
        
        return (new SymfonyRoute($uri, $optionals, $this->route->wheres, [], $this->route->domain() ?  : ''))->compile();
    }

    /**
     * Get the optional parameters for the route.
     *
     * @return array
     */
    protected function getOptionalParameters()
    {
        preg_match_all('/\{(\w+?)\?\}/', $this->route->uri(), $matches);
        
        return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
    }
}
