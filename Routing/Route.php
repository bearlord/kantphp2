<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Routing;

use ReflectionFunction;
use Kant\Kant;
use Kant\Support\Arr;
use Kant\Support\Str;
use Kant\Helper\StringHelper;
use Kant\Http\Request;
use Kant\Routing\Matching\UriValidator;
use Kant\Routing\Matching\HostValidator;
use Kant\Routing\Matching\MethodValidator;
use Kant\Routing\Matching\SchemeValidator;
use Kant\Exception\InvalidConfigException;

class Route
{

    use RouteDependencyResolverTrait;

    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    public $uri;

    /**
     * The HTTP methods the route responds to.
     *
     * @var array
     */
    public $methods;

    /**
     * The route action array.
     *
     * @var array
     */
    public $action;

    /**
     * The default values for the route.
     *
     * @var array
     */
    public $defaults = [];

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The controller instance.
     *
     * @var mixed
     */
    public $controller;

    /**
     *
     * The Controller suffix
     * 
     * @var type
     */
    public $controllerSuffix = "Controller";

    /**
     * The Action suffix
     * 
     * @var type
     */
    public $actionSuffix = 'Action';

    /**
     * The computed gathered middleware.
     *
     * @var array|null
     */
    public $computedMiddleware;

    /**
     * The compiled version of the route.
     *
     * @var \Symfony\Component\Routing\CompiledRoute
     */
    public $compiled;

    /**
     * The validators used by the routes.
     *
     * @var array
     */
    public static $validators;

    /**
     * Create a new Route instance.
     *
     * @param array|string $methods            
     * @param string $uri            
     * @param \Closure|array $action            
     * @return void
     */
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);

        if (in_array('GET', $this->methods) && !in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }

        if (isset($this->action['prefix'])) {
            $this->prefix($this->action['prefix']);
        }
    }

    /**
     * Parse the route action into a standard array.
     *
     * @param callable|array|null $action            
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    protected function parseAction($action)
    {
        return RouteAction::parse($this->uri, $action);
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     */
    public function run()
    {
        try {
            if ($this->isControllerAction()) {
                return $this->runController();
            }

            return $this->runCallable();
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Checks whether the route's action is a controller.
     *
     * @return bool
     */
    protected function isControllerAction()
    {
        return is_string($this->action['uses']);
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     *
     * @throws \Kant\Exception\NotFoundHttpException
     */
    protected function runController()
    {
        $controller = $this->getController();
        $controller->dispatcher = $this->getDispatcher();
        Kant::$app->view->dispatcher = $this->getDispatcher();

        return (new ControllerDispatcher())->dispatch($this, $controller, $this->getControllerMethod());
    }

    protected function getDispatcher()
    {
        $class = StringHelper::basename($this->parseControllerCallback()[0], $this->controllerSuffix);
        $method = StringHelper::basename($this->parseControllerCallback()[1], $this->actionSuffix);
        $module = explode('\\', $this->parseControllerCallback()[0])[2];

        return sprintf("%s/%s/%s", $module, $class, $method);
    }

    /**
     * Get the controller instance for the route.
     *
     * @return mixed
     */
    public function getController()
    {
        $class = $this->parseControllerCallback()[0];
        if (!$this->controller) {

            if (is_subclass_of($class, \Kant\Controller\Controller::className())) {
                $this->controller = Kant::createObject($class);
            } elseif (KANT_DEBUG) {
                throw new InvalidConfigException("Controller class must extend from \\Kant\\Controller\\Controller: $class.");
            } else {
                return null;
            }
        }

        return $this->controller;
    }

    /**
     * Get the controller method used for the route.
     *
     * @return string
     */
    protected function getControllerMethod()
    {
        // return $this->parseControllerCallback()[1] . $this->actionSuffix;
        return $this->parseControllerCallback()[1];
    }

    /**
     * Parse the controller.
     *
     * @return array
     */
    protected function parseControllerCallback()
    {
        return Str::parseCallback($this->action['uses']);
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     */
    protected function runCallable()
    {
        $parameters = $this->resolveMethodDependencies($this->parametersWithoutNulls(), new ReflectionFunction($this->action['uses']));

        return call_user_func_array($this->action['uses'], $parameters);
    }

    /**
     * Get the HTTP verbs the route responds to.
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Determine if the route only responds to HTTP requests.
     *
     * @return bool
     */
    public function httpOnly()
    {
        return in_array('http', $this->action, true);
    }

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function httpsOnly()
    {
        return $this->secure();
    }

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function secure()
    {
        return in_array('https', $this->action, true);
    }

    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function domain()
    {
        return isset($this->action['domain']) ? str_replace([
                    'http://',
                    'https://'
                        ], '', $this->action['domain']) : null;
    }

    /**
     * Get the URI associated with the route.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Add a prefix to the route URI.
     *
     * @param string $prefix            
     * @return $this
     */
    public function prefix($prefix)
    {
        $uri = rtrim($prefix, '/') . '/' . ltrim($this->uri, '/');

        $this->uri = trim($uri, '/');

        return $this;
    }

    /**
     * Get the route validators for the instance.
     *
     * @return array
     */
    public static function getValidators()
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }

        // To match the route, we will use a chain of responsibility pattern with the
        // validator implementations. We will spin through each one making sure it
        // passes and then we will know if the route as a whole matches request.
        return static::$validators = [
            new UriValidator(),
            new MethodValidator(),
            new SchemeValidator(),
            new HostValidator()
        ];
    }

    /**
     * Get the compiled version of the route.
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function getCompiled()
    {
        return $this->compiled;
    }

    /**
     * Set the router instance on the route.
     *
     * @param \Kant\Routing\Router $router            
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Determine if the route matches given request.
     *
     * @param \Kant\Http\Request $request            
     * @param bool $includingMethod            
     * @return bool
     */
    public function matches(Request $request, $includingMethod = true)
    {
        $this->compileRoute();

        foreach ($this->getValidators() as $validator) {
            if (!$includingMethod && $validator instanceof MethodValidator) {
                continue;
            }

            if (!$validator->matches($this, $request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compile the route into a Symfony CompiledRoute instance.
     *
     * @return void
     */
    protected function compileRoute()
    {
        if (!$this->compiled) {
            $this->compiled = (new RouteCompiler($this))->compile();
        }

        return $this->compiled;
    }

    /**
     * Bind the route to a given request for execution.
     *
     * @param \Kant\Http\Request $request            
     * @return $this
     */
    public function bind(Request $request)
    {
        // $this->compileRoute();
        $this->parameters = (new RouteParameterBinder($this))->parameters($request);

        return $this;
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function parameters()
    {
        if (isset($this->parameters)) {
            return $this->parameters;
        }

        throw new LogicException('Route is not bound.');
    }

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function parametersWithoutNulls()
    {
        return array_filter($this->parameters(), function ($p) {
            return !is_null($p);
        });
    }

    /**
     * Get all of the parameter names for the route.
     *
     * @return array
     */
    public function parameterNames()
    {
        if (isset($this->parameterNames)) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParameterNames();
    }

    /**
     * Get the parameter names for the route.
     *
     * @return array
     */
    protected function compileParameterNames()
    {
        preg_match_all('/\{(.*?)\}/', $this->domain() . $this->uri, $matches);

        return array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);
    }

    /**
     * Set a regular expression requirement on the route.
     *
     * @param array|string $name            
     * @param string $expression            
     * @return $this
     */
    public function where($name, $expression = null)
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param array|string $name            
     * @param string $expression            
     * @return array
     */
    protected function parseWhere($name, $expression)
    {
        return is_array($name) ? $name : [
            $name => $expression
        ];
    }

    /**
     * Get the action array for the route.
     *
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the action array for the route.
     *
     * @param array $action            
     * @return $this
     */
    public function setAction(array $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware()
    {
        if (!is_null($this->computedMiddleware)) {
            return $this->computedMiddleware;
        }

        $this->computedMiddleware = [];

        return $this->computedMiddleware = array_unique(array_merge($this->middleware(), $this->controllerMiddleware()), SORT_REGULAR);
    }

    /**
     * Get or set the middlewares attached to the route.
     *
     * @param array|string|null $middleware            
     * @return $this|array
     */
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return (array) Arr::get($this->action, 'middleware', []);
        }

        if (is_string($middleware)) {
            $middleware = func_get_args();
        }

        $this->action['middleware'] = array_merge((array) Arr::get($this->action, 'middleware', []), $middleware);

        return $this;
    }

    /**
     * Get the middleware for the route's controller.
     *
     * @return array
     */
    public function controllerMiddleware()
    {
        if (!$this->isControllerAction()) {
            return [];
        }

        return ControllerDispatcher::getMiddleware($this->getController(), $this->getControllerMethod());
    }

    /**
     * Get the name of the route instance.
     *
     * @return string
     */
    public function getName()
    {
        return isset($this->action['as']) ? $this->action['as'] : null;
    }

}
