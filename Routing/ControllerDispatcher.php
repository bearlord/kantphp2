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
    protected $actionSuffix;

    /**
     * Dispatch a request to a given controller and method.
     *
     * @param \Kant\Routing\Route $route            
     * @param \Kant\Controller\Controller $controller            
     * @param string $method            
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method) {
        $parameters = $this->resolveClassMethodDependencies($route->parametersWithoutNulls(), $controller, $method);

        if (!empty($route->middleware())) {
            $id = strtolower($route->middleware()[0]);
        } else {
            $id = strtolower(explode("\\", get_class($controller))[1]);
        }

        $controller->setIdOptions([
            'id' => strtolower(str_replace('Controller', '', basename(get_class($controller)))),
//            'module' => $controller
        ]);

        $oldController = Kant::$app->controller;
        Kant::$app->controller = $controller;

        $result = Kant::$container->call([
            $controller,
            'runActions'
                ], [
            $method,
            $parameters
        ]);

        if ($oldController !== null) {
            Kant::$app->controller = $oldController;
        }
        return $result;
    }

    /**
     * Get the middleware for the controller instance.
     *
     * @param \Kant\Routing\Controller $controller            
     * @param string $method            
     * @return array
     */
    public static function getMiddleware($controller, $method) {
        if (!method_exists($controller, 'getMiddleware')) {
            return [];
        }

        return (new Collection($controller->getMiddleware()))->reject(function ($data) use($method) {
                            return static::methodExcludedByOptions($method, $data['options']);
                        })
                        ->pluck('middleware')
                        ->all();
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param string $method            
     * @param array $options            
     * @return bool
     */
    protected static function methodExcludedByOptions($method, array $options) {
        return (isset($options['only']) && !in_array($method, (array) $options['only'])) || (!empty($options['except']) && in_array($method, (array) $options['except']));
    }

}
