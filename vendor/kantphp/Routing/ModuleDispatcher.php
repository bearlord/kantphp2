<?php

namespace Kant\Routing;

use Kant\Kant;
use Kant\Factory;
use Kant\Registry\KantRegistry;
use ReflectionMethod;
use ReflectionParameter;

class ModuleDispatcher {

    use RouteDependencyResolverTrait;

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
    public function dispatch(\Kant\Http\Request $request) {
        $dispatcher = $this->parseUrl($request->path());
        return $this->module($dispatcher['route']);
    }

    /**
     * Parse url to module parament
     * @param type $url
     */
    public function parseUrl($url) {
        $result = $this->parseRoute($url);
        return $result;
    }

    /**
     * Parse route
     * 
     * @param type $pathinfo
     */
    protected static function parseRoute($pathinfo) {
        $route = [null, null, null];
        $var = [];
        $pathinfo = trim($pathinfo, "/");
        //Special pathinof as demo/index/get/a,100/b,101?c=102&d=103
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
            //Normal pathinfo as demo/index/get/a,100/b,101
            $path = explode('/', $pathinfo);
        }

        $routeConfig = Kant::$app->config->get("route");
        $module = array_shift($path);
        $module = !empty($module) ? $module : $routeConfig['module'];
        $controller = !empty($path) ? array_shift($path) : $routeConfig['ctrl'];
        $action = !empty($path) ? array_shift($path) : $routeConfig['act'];
        if ($action) {
            if (strpos($action, "?") !== false) {
                $action = substr($action, 0, strpos($action, "?"));
            }
            $urlsuffix = Kant::$app->config->get('urlSuffix');
            if ($urlsuffix) {
                if (strpos($action, "&") !== false) {
                    $action = substr($action, 0, strpos($action, $urlsuffix));
                }
            } else {
                if (strpos($action, "&") !== false) {
                    $action = substr($action, 0, strpos($action, "&"));
                }
            }
            while ($next = array_shift($path)) {
                $query = preg_split("/[?&]/", $next);
                if (!empty($query)) {
                    foreach ($query as $key => $val) {
                        $arr = preg_split("/[,:=-]/", $val, 2);
                        if (!empty($arr[1])) {
                            $var[$arr[0]] = urldecode($arr[1]);
                        }
                    }
                }
            }
        }

        $route = [$module, $controller, $action];
        return ['route' => $route, 'var' => $var];
    }

    /**
     * Execution
     * 
     * @throws KantException
     * @throws ReflectionException
     */
    public function module($dispatcher) {
        KantRegistry::set('dispatcher', $dispatcher);
        var_dump($dispatcher);
        //module name
        $moduleName = $this->getModuleName($dispatcher[0]);
        if (empty($moduleName)) {
            throw new KantException('No Module found');
        }
        $this->setModuleConfig($moduleName);


        //controller name
        $controllerName = $this->getControllerName($dispatcher[1]);
        $controller = $this->controller($controllerName, $moduleName);
        if (!$controller) {
            if (empty($controller)) {
                throw new KantException(sprintf("No controller exists:%s", ucfirst($dispatcher[1]) . 'Controller'));
            }
        }


        //action name
        $action = $dispatcher[2] ?: ucfirst(Factory::getConfig()->get('route.act'));
        $data = $this->callClass($controller . "@" . $action . Factory::getConfig()->get('actionSuffix'));
        return $data;
    }

    /**
     * Init Module Config;
     * @param type $module
     */
    protected function setModuleConfig($module) {
        $configFilePath = MODULE_PATH . $module . DIRECTORY_SEPARATOR . 'Config.php';
        if (file_exists($configFilePath)) {
            Factory::getConfig()->merge(require $configFilePath);
        }
    }

    /**
     * Get module name
     * 
     * @param string $name
     * @return string
     */
    protected function getModuleName($name) {
        return ucfirst($name ?: Factory::getConfig()->get('route.module'));
    }

    /**
     * Get controller name
     * 
     * @param string $name
     * @return string
     */
    protected function getControllerName($name) {
        return ucfirst($name ?: Factory::getConfig()->get('route.ctrl'));
    }

    /**
     * Controller
     * 
     * @staticvar array $classes
     * @return boolean|array|\classname
     * @throws KantException
     */
    protected function controller($controller, $module) {
        $controller = ucfirst($controller) . "Controller";
        $filepath = APP_PATH . "Module/{$module}/Controller/{$controller}.php";
        if (!file_exists($filepath)) {
            throw new KantException(sprintf("File does not exists:%s", $filepath));
        }
        include $filepath;

        $namespace = "App\\{$module}\\Controller\\";
        $controller = $namespace . $controller;
        return $controller;
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param  callable|string  $callback
     * @param  array  $parameters
     * @param  string|null  $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null) {
        if ($this->isCallableWithAtSign($callback) || $defaultMethod) {
            return $this->callClass($callback, $parameters, $defaultMethod);
        }
        $dependencies = $this->getMethodDependencies($callback, $parameters);
        return call_user_func_array($callback, $dependencies);
    }

    /**
     * Determine if the given string is in Class@method syntax.
     *
     * @param  mixed  $callback
     * @return bool
     */
    protected function isCallableWithAtSign($callback) {
        return is_string($callback) && strpos($callback, '@') !== false;
    }

    /**
     * Get all dependencies for a given method.
     *
     * @param  callable|string  $callback
     * @param  array  $parameters
     * @return array
     */
    protected function getMethodDependencies($callback, array $parameters = []) {
        $dependencies = [];

        foreach ($this->getCallReflector($callback)->getParameters() as $parameter) {
            $this->addDependencyForCallParameter($parameter, $parameters, $dependencies);
        }

        return array_merge($dependencies, $parameters);
    }

    /**
     * Get the proper reflection instance for the given callback.
     *
     * @param  callable|string  $callback
     * @return \ReflectionFunctionAbstract
     */
    protected function getCallReflector($callback) {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }
        if (is_array($callback)) {
            list($class, $name) = $callback;
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
     * @param  \ReflectionParameter  $parameter
     * @param  array  $parameters
     * @param  array  $dependencies
     * @return mixed
     */
    protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies) {
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
     * @param  string  $target
     * @param  array  $parameters
     * @param  string|null  $defaultMethod
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function callClass($target, array $parameters = [], $defaultMethod = null) {
        $segments = explode('@', $target);

        // If the listener has an @ sign, we will assume it is being used to delimit
        // the class name from the handle method name. This allows for handlers
        // to run multiple handler methods in a single class for convenience.
        $method = count($segments) == 2 ? $segments[1] : $defaultMethod;

        if (is_null($method)) {
            throw new InvalidArgumentException('Method not provided.');
        }
//        return $this->call([$this->make($segments[0]), $method], $parameters);

        return $this->call([Kant::$container->get($segments[0]), $method], $parameters);
    }

    /**
     * Resolve the given type from the container.
     */
    public function make($class) {
        return Kant::createObject($class);
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
