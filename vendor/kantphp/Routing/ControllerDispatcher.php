<?php

namespace Kant\Routing;

use Kant\Kant;

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

        return Kant::$container->callClass($controller::className() . "@" . 'runAction', [$method, $parameters]);
    }

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

        return collect($controller->getMiddleware())->reject(function ($data) use ($method) {
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
