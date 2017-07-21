<?php

namespace Kant\Routing;

use Kant\Kant;
use Kant\Http\Request;
use Kant\Registry\KantRegistry;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionException;
use Kant\Exception\KantException;

class ModuleDispatcher {

    use RouteDependencyResolverTrait;

    /**
     *
     * The Controller suffix
     * @var type 
     */
    public $controllerSuffix = "Controller";

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
    public function dispatch(Request $request) {
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
        //module name
        $moduleName = $this->getModuleName($dispatcher[0]);
        if (empty($moduleName)) {
            throw new KantException('No Module found');
        }
        Kant::$app->setModuleConfig($moduleName);
        
        Kant::$app->setDispatcher('implicit', $dispatcher);

        //controller name
        $controllerName = $this->getControllerName($dispatcher[1]);
        $controller = $this->getControllerClass($controllerName, $moduleName);
        if (!$controller) {
            if (empty($controller)) {
                throw new KantException(sprintf("No controller exists:%s", ucfirst($dispatcher[1]) . $this->controllerSuffix));
            }
        }


        //action name
        $action = $dispatcher[2] ?: ucfirst(Kant::$app->config->get('route.act'));

        $data = Kant::$container->callClass($controller . "@" . 'runAction', [$action]);
        return $data;
    }

    /**
     * Get module name
     * 
     * @param string $name
     * @return string
     */
    protected function getModuleName($name) {
        return ucfirst($name ?: Kant::$app->config->get('route.module'));
    }

    /**
     * Get controller name
     * 
     * @param string $name
     * @return string
     */
    protected function getControllerName($name) {
        return ucfirst($name ?: Kant::$app->config->get('route.ctrl'));
    }

    /**
     * Controller
     * 
     * @staticvar array $classes
     * @return boolean|array|\classname
     * @throws KantException
     */
    protected function getControllerClass($controllerName, $moduleName) {
        return "App\\{$moduleName}\\Controllers\\" . ucfirst($controllerName) . $this->controllerSuffix ;
    }
}
