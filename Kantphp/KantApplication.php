<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

use Kant\KantFactory;
use Kant\Route\Route;
use Kant\Http\Response;
use Kant\Registry\KantRegistry;
use Kant\Build\Build;
use Kant\Log\Log;
use Kant\Runtime\Runtime;
use Kant\Exception\KantException;
use ReflectionException;
use ReflectionMethod;
use Kant\Http\Request;
use Kant\Session\Session;
use Kant\Cache\Cache;

final class KantApplication {

    private static $_instance = null;
    private $_environment = 'Development';

    /**
     * defined dispath
     * @var array 
     */
    private static $dispatch = [];

    /**
     * Run time config
     *
     * @var Kant_Config
     */
    public static $configObj;

    /**
     * Run time config's reference, for better performance
     *
     * @var array
     */
    protected static $config;

    /**
     * Dispathc info
     *
     * @var array
     */
    protected $dispatchInfo = null;
    protected $outputType = [
        'html' => 'text/html',
        'json' => 'application/json',
        'jsonp' => 'application/javascript',
        'xml' => 'text/xml'
    ];

    /**
     * Constructs
     */
    public function __construct($environment) {
        $this->_environment = $environment;
        $this->_init();
    }

    /**
     * Init
     */
    private function _init() {
        $this->_initConfig();
        $this->_initSession();
        Cache::platform("file");
    }

    /**
     * Init Config
     */
    private function _initConfig() {
        /**
         * App config
         */
        $appConfig = include CFG_PATH . $this->_environment . DIRECTORY_SEPARATOR . 'Config.php';
        /**
         * Route config
         */
        $routeConfig = include CFG_PATH . $this->_environment . DIRECTORY_SEPARATOR . 'Route.php';
        /**
         * Merge app and route config
         */
        KantFactory::getConfig()->merge($appConfig)->merge(['route' => $routeConfig]);
        KantFactory::getConfig()->set([
            'environment' => $this->_environment,
            'config_path' => CFG_PATH . $this->_environment . DIRECTORY_SEPARATOR         
        ]);
    }

    /**
     * Init Module Config;
     * @param type $module
     */
    private function _initModuleConfig($module) {
        $configFilePath = MODULE_PATH . $module . DIRECTORY_SEPARATOR . 'Config.php';
        if (!file_exists($configFilePath)) {
            return false;
        }
        $moduleConfig = include $configFilePath;
        KantFactory::getConfig()->merge($moduleConfig);
    }

    /**
     * Initialize session
     * 
     * @staticvar type $session
     * @return type
     */
    private function _initSession() {
        static $session = null;
        if (empty($session)) {
            $sessionConfig = KantFactory::getConfig()->get('session');
            $sessionAdapter = 'default';
            try {
                $session = Session::getInstance($sessionConfig)->getSession($sessionAdapter);
            } catch (RuntimeException $e) {
                throw new KantException($e->getMessage());
            }
        }
        return $session;
    }
    
    /**
     * Singleton instance
     * 
     * @param type $environment
     * @return type
     */
    public static function getInstance($environment = 'Development') {
        if (null === self::$_instance) {
            self::$_instance = new self($environment);
        }
        return self::$_instance;
    }

    /**
     * Boot
     * 
     */
    public function boot() {
        $this->parpare();
        $this->route();
        $this->dispatch();
        $this->end();
    }

    /**
     * Parpare
     */
    protected function parpare() {
        //Default timezone
        date_default_timezone_set(KantFactory::getConfig()->get('default_timezone'));
        if (KantFactory::getConfig()->get('debug')) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            Runtime::mark('begin');
        }
        //load common file
        require_once APP_PATH . 'Bootstrap.php';
        //Build Module
        $this->buildModule();
        //Logfile initialization
        Log::init(array(
            'type' => 'File',
            'log_path' => LOG_PATH
        ));
    }

    /**
     * End
     */
    protected function end() {
        if (KantFactory::getConfig()->get('debug')) {
            Runtime::mark('end');
        }
    }

    /**
     * Route
     */
    protected function route() {
        //remove url suffix
        $pathinfo = str_replace(KantFactory::getConfig()->get('url_suffix'), '', $this->parsePathinfo());
        Route::import(KantFactory::getConfig()->get('route'));
        $dispath = Route::check($pathinfo);
        if ($dispath === false) {
            $dispath = Route::parseUrl($pathinfo);
        }
        self::$dispatch = $dispath;
    }

    /**
     * Parse Pathinfo
     */
    protected function parsePathinfo() {
        $pathinfo = KantFactory::getPathInfo()->parsePathinfo();
        return $pathinfo;
    }

    /**
     * Dispatch
     */
    protected function dispatch() {
        $data = [];
        switch (self::$dispatch['type']) {
            case 'redirect':
                header('Location: ' . self::$dispatch['url'], true, self::$dispatch['status']);
                break;
            case 'module':
                $data = self::module(self::$dispatch['module']);
                break;
            case 'controller':
                $data = Loader::action(self::$dispatch['controller'], self::$dispatch['params']);
                break;
            case 'method':
                $data = self::invokeMethod(self::$dispatch['method'], self::$dispatch['params']);
                break;
            case 'function':
                $data = self::invokeFunction(self::$dispatch['function'], self::$dispatch['params']);
                break;
            default:
                throw new KantException('dispatch type not support', 5002);
        }
        $this->output($data);
    }

    /**
     * Output
     * 
     * @param type $data
     */
    protected function output($data) {
        $type = strtolower(KantFactory::getConfig()->get('default_return_type'));
        if (in_array($type, array_keys($this->outputType)) == false) {
            throw new KantException("Unsupported output type:" . $type);
        }
        $classname = "Kant\\Http\\" . ucfirst($type);
        $OutputObj = new $classname;
        $method = new ReflectionMethod($OutputObj, 'output');
        $result = $method->invokeArgs($OutputObj, array($data));
        Response::create($result, Response::HTTP_OK, [
            'Content-Type' => $this->outputType[$type]
        ])->send();
    }

    /**
     * Invoke Function
     * 
     * @param type $function
     * @param type $vars
     * @return type
     */
    public static function invokeFunction($function, $vars = []) {
        $reflect = new \ReflectionFunction($function);
        $args = self::bindParams($reflect, $vars);
        return $reflect->invokeArgs($args);
    }

    /**
     * Bind Params
     * 
     * @param type $reflect
     * @param type $vars
     * @return type
     * @throws Exception
     */
    private static function bindParams($reflect, $vars) {
        $args = [];
        $type = key($vars) === 0 ? 1 : 0;
        if ($reflect->getNumberOfParameters() > 0) {
            $params = $reflect->getParameters();
            foreach ($params as $param) {
                $name = $param->getName();
                if (1 == $type && !empty($vars)) {
                    $args[] = array_shift($vars);
                } elseif (0 == $type && isset($vars[$name])) {
                    $args[] = $vars[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new KantException('method param miss:' . $name, 5004);
                }
            }
        }
        return $args;
    }

    /**
     * Execution
     * 
     * @throws KantException
     * @throws ReflectionException
     */
    public function module($dispatchInfo) {
        KantRegistry::set('dispatchInfo', $dispatchInfo);
        $this->dispatchInfo = $dispatchInfo;
        //module name
        $moduleName = !empty($dispatchInfo[0]) ? ucfirst($dispatchInfo[0]) : ucfirst(KantFactory::getConfig()->get('route.module'));
        if (empty($moduleName)) {
            throw new KantException('No Module found');
        }
        $this->_initModuleConfig($moduleName);
        //controller name
        $controllerName = !empty($dispatchInfo[1]) ? ucfirst($dispatchInfo[1]) : ucfirst(KantFactory::getConfig()->get('route.ctrl'));
        $controller = $this->controller($controllerName, $moduleName);
        if (!$controller) {
            if (empty($controller)) {
                throw new KantException(sprintf("No controller exists:%s", ucfirst($this->dispatchInfo[1]) . 'Controller'));
            }
        }
        $action = !empty($this->dispatchInfo[2]) ? $this->dispatchInfo[2] . KantFactory::getConfig()->get('action_suffix') : 'Index' . KantFactory::getConfig()->get('action_suffix');
        try {
            if (!preg_match('/^[A-Za-z](\w)*$/', $action)) {
                throw new ReflectionException();
            }
            $method = new ReflectionMethod($controller, $action);
            if ($method->isPublic() && !$method->isStatic()) {
                $request = Request::createFromBase(Request::createFromGlobals());
                $data = $method->invoke($controller, $request);
            } else {
                throw new ReflectionException();
            }
        } catch (ReflectionException $e) {
            $method = new ReflectionMethod($controller, '__call');
            $request = Request::createFromBase(Request::createFromGlobals());
            $data = $method->invokeArgs($controller, array($action, $request));
        }
        return $data;
    }

    /**
     * Controller
     * 
     * @staticvar array $classes
     * @return boolean|array|\classname
     * @throws KantException
     */
    protected function controller($controller = '', $module) {
        if (empty($controller)) {
            $controller = ucfirst($this->dispatchInfo[1]) . 'Controller';
        } else {
            $controller = ucfirst($controller) . "Controller";
        }
        $filepath = APP_PATH . "Module/" . $module . "/Controller/$controller.php";
        if (!file_exists($filepath)) {
            throw new KantException(sprintf("File does not exists:%s", $filepath));
        }
        include $filepath;
        $namespace = "App\\$module\\Controller\\";
        $controller = $namespace . $controller;
        if (class_exists($controller)) {
            $class = new $controller;
            return $class;
        }
    }

    /**
     * Build module
     */
    public function buildModule() {
        //build module
        if (KantFactory::getConfig()->get('check_app_dir')) {
            if (!defined('CREATE_MODULE')) {
                return;
            }
            $module = CREATE_MODULE;
            if (is_dir(MODULE_PATH . $module) == false) {
                Build::checkDir($module);
            }
        }
    }

}
