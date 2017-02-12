<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

use Kant\Foundation\Component;
use Kant\Di\ServiceLocator;
use Kant\Helper\ArrayHelper;
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
use Kant\Cache\Cache;
use InvalidArgumentException;
use ReflectionParameter;

class KantApplication extends ServiceLocator {

    private static $_instance = null;

    /**
     * defined dispath
     * @var array 
     */
    private static $dispatch = [];

    /**
     * @var string the language that is meant to be used for end users. It is recommended that you
     * use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
     * for English, while `en-US` stands for English (United States).
     * @see sourceLanguage
     */
    public $language = 'zh-CN';

    /**
     * @var string the language that the application is written in. This mainly refers to
     * the language that the messages and view files are written in.
     * @see language
     */
    public $sourceLanguage = 'zh-CN';

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
    public function __construct($env) {
        Kant::$app = $this;

        $config = $this->initConfig($env);
        $this->preInit($config);

        $this->initSession($config['session']);
        $this->initCache($config['cache']);

        $this->setDb();
    }

    /**
     * Init Config
     */
    protected function initConfig($environment) {
        $appConfig = ArrayHelper::merge(
                        require KANT_PATH . DIRECTORY_SEPARATOR . 'Config/Convention.php', require CFG_PATH . $environment . DIRECTORY_SEPARATOR . 'Config.php', require CFG_PATH . $environment . DIRECTORY_SEPARATOR . 'Route.php', [
                    'environment' => $environment,
                    'config_path' => CFG_PATH . $environment . DIRECTORY_SEPARATOR
                        ]
        );
        return KantFactory::getConfig()->merge($appConfig)->reference();
    }

    /**
     * Init Module Config;
     * @param type $module
     */
    private function _initModuleConfig($module) {
        $configFilePath = MODULE_PATH . $module . DIRECTORY_SEPARATOR . 'Config.php';
        if (file_exists($configFilePath)) {
            KantFactory::getConfig()->merge(require $configFilePath);
        }
    }

    /**
     * Initialize session
     * 
     * @staticvar type $session
     * @return type
     */
    protected function initSession($config) {
        return KantFactory::getSession($config);
    }

    /**
     * Initialize cache
     * 
     * @param type $config
     * @return type
     */
    protected function initCache($config) {
        return Cache::platform($config);
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
    public function run() {
        $type = strtolower(KantFactory::getConfig()->get('default_return_type'));

        $request = Kant::$container->instance('Kant\Http\Request', Request::capture());
        $data = $this->dispatch($this->route($request->path()));

        $result = $this->parseData($data, $type);
        Response::create($result, Response::HTTP_OK, [
            'Content-Type' => $this->outputType[$type]
        ])->send();

        $this->end();
    }

    /**
     * Parpare
     */
    protected function preInit($config) {
        //set default timezone
        if (isset($config['timeZone'])) {
            $this->setTimeZone($config['timeZone']);
        } elseif (!ini_get('date.timezone')) {
            $this->setTimeZone('UTC');
        }

        $this->setLanguage($config['language']);

        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($components['components'][$id])) {
                $components['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($components['components'][$id]['class'])) {
                $components['components'][$id]['class'] = $component['class'];
            }
        }
        Component::__construct($components);

        if ($config['debug']) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            Runtime::mark('begin');
        }
        //load common file
        require_once APP_PATH . 'Bootstrap.php';

        //Logfile initialization
        Log::init(array(
            'type' => 'File',
            'log_path' => LOG_PATH
        ));
    }

    /**
     * set the language that is meant to be used for end users. It is recommended that you
     * use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
     * for English, while `en-US` stands for English (United States).
     * 
     */
    public function setLanguage($value) {
        $this->language = $value;
    }

    /**
     * get the language that is meant to be used for end users.
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Returns the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_get().
     * If time zone is not configured in php.ini or application config,
     * it will be set to UTC by default.
     * @return string the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-get.php
     */
    public function getTimeZone() {
        return date_default_timezone_get();
    }

    /**
     * Sets the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * @param string $value the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     */
    public function setTimeZone($value) {
        date_default_timezone_set($value);
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
    protected function route($path) {
        //remove url suffix
        $pathinfo = str_replace(KantFactory::getConfig()->get('url_suffix'), '', $path);

        Route::import(KantFactory::getConfig()->get('route'));
        $dispath = Route::check($pathinfo);
        if ($dispath === false) {
            $dispath = Route::parseUrl($pathinfo);
        }
        return $dispath;
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
    protected function dispatch($dispatch) {
        $data = [];
        switch ($dispatch['type']) {
            case 'redirect':
                header('Location: ' . $dispatch['url'], true, $dispatch['status']);
                break;
            case 'module':
                $data = self::module($dispatch['module']);
                break;
            case 'controller':
                $data = Loader::action($dispatch['controller'], $dispatch['params']);
                break;
            case 'method':
                $data = self::invokeMethod($dispatch['method'], $dispatch['params']);
                break;
            case 'function':
                $data = self::invokeFunction($dispatch['function'], $dispatch['params']);
                break;
            default:
                throw new KantException('dispatch type not support', 5002);
        }
        return $data;
    }

    /**
     * Parse Data
     * 
     * @param type $data
     * @param type $type
     * @return type
     * @throws KantException
     */
    protected function parseData($data, $type) {
        if (in_array($type, array_keys($this->outputType)) == false) {
            throw new KantException("Unsupported output type:" . $type);
        }
        $classname = "Kant\\Http\\" . ucfirst($type);
        $OutputObj = new $classname;
        $method = new ReflectionMethod($OutputObj, 'output');
        $result = $method->invokeArgs($OutputObj, array($data));
        return $result;
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
        $moduleName = ucfirst($dispatchInfo[0]) ?: ucfirst(KantFactory::getConfig()->get('route.module'));
        if (empty($moduleName)) {
            throw new KantException('No Module found');
        }
        $this->_initModuleConfig($moduleName);

        //controller name
        $controllerName = ucfirst($dispatchInfo[1]) ?: ucfirst(KantFactory::getConfig()->get('route.ctrl'));
        $controller = $this->controller($controllerName, $moduleName);
        if (!$controller) {
            if (empty($controller)) {
                throw new KantException(sprintf("No controller exists:%s", ucfirst($this->dispatchInfo[1]) . 'Controller'));
            }
        }
        //action name
        $action = $this->dispatchInfo[2] ?: 'Index';
        $data = $this->callClass($controller . "@" . $action . KantFactory::getConfig()->get('action_suffix'));
        return $data;
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
        $filepath = APP_PATH . "Module/" . $module . "/Controller/$controller.php";
        if (!file_exists($filepath)) {
            throw new KantException(sprintf("File does not exists:%s", $filepath));
        }
        include $filepath;
        
        $namespace = "App\\$module\\Controller\\";
        $controller = $namespace . $controller;
        return $controller;
    }

    /**
     * Returns the configuration of core application components.
     * @see set()
     */
    public function coreComponents() {
        return [
            'i18n' => ['class' => 'Kant\I18n\I18N'],
        ];
    }

    /**
     * Set the database connection component.
     */
    public function setDb() {
        $dbConfig = KantFactory::getConfig()->get('database');
        foreach ($dbConfig as $key => $config) {
            $this->set($key, array_merge([
                'class' => 'Kant\Database\Connection'
                            ], $config));
        }
    }

    /**
     * Returns the database connection component.
     * @return \Kant\Database\Connection the database connection.
     */
    public function getDb() {
        return $this->get('db');
    }

    /**
     * Returns the internationalization (i18n) component
     * @return \yii\i18n\I18N the internationalization application component.
     */
    public function getI18n() {
        return $this->get('i18n');
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

}
