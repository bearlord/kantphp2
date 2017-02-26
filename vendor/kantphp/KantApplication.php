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
use Kant\Factory;
use Kant\Route\Route;
use Kant\Http\Request;
use Kant\Http\Response;
use Kant\Registry\KantRegistry;
use Kant\Runtime\Runtime;
use Kant\Exception\KantException;
use ReflectionException;
use ReflectionMethod;
use InvalidArgumentException;
use ReflectionParameter;

class KantApplication extends ServiceLocator {

    private static $_instance = null;
    public $config;
    public $env = 'Dev';

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
    protected $dispatcher = null;
    protected $outputType = [
        'html' => 'text/html',
        'json' => 'application/json',
        'jsonp' => 'application/javascript',
        'xml' => 'text/xml'
    ];

    /**
     * Constructs
     * Initialize Config,register Cache,Database,Session,Cookie
     * 
     * @param string $env
     */
    public function __construct($env) {
        Kant::$app = $this;
        $this->env = $env;
        $this->config = $config = $this->initConfig($env);
        $this->preInit($config);
    }

    /**
     * Returns the configuration of core application components.
     * @see set()
     */
    public function coreComponents() {
        return [
            'log' => ['class' => 'Kant\Log\Dispatcher'],
            'i18n' => ['class' => 'Kant\I18n\I18N'],
            'files' => ['class' => 'Kant\Filesystem\Filesystem']
        ];
    }

    /**
     * Init Config
     */
    protected function initConfig($env) {
        $appConfig = ArrayHelper::merge(
                        require KANT_PATH . DIRECTORY_SEPARATOR . 'Config/Convention.php', require CFG_PATH . $env . DIRECTORY_SEPARATOR . 'Config.php', require CFG_PATH . $env . DIRECTORY_SEPARATOR . 'Route.php', [
                    'environment' => $env,
                    'config_path' => CFG_PATH . $env . DIRECTORY_SEPARATOR
                        ]
        );
        return Factory::getConfig()->merge($appConfig)->reference();
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
     * Initialize session
     * 
     * @staticvar type $session
     * @return type
     */
    protected function setSession($config, $request, $response) {
        $this->set('session', (new Session\Session($config, $request, $response))->handle());
    }

    /**
     * Get session instance
     * 
     * @return object
     */
    public function getSession() {
        return $this->get('session');
    }

    /**
     * Register cache
     * 
     * @param type $config
     * @return null
     */
    protected function setCache($config) {
        return $this->set('cache', \Kant\Cache\Cache::register($config));
    }

    /**
     * Get cache instance
     * 
     * @return object
     */
    public function getCache() {
        return $this->get('cache');
    }

    /**
     * Register Cookie
     */
    protected function setCookie($config, $request, $response) {
        $this->set('cookie', (new Cookie\Cookie($config, $request, $response)));
    }

    /**
     * Get Cookie instance
     * @return object
     */
    public function getCookie() {
        return $this->get('cookie');
    }

    /**
     * Singleton instance
     * 
     * @param type $environment
     * @return type
     */
    public static function getInstance($environment = 'Dev') {
        if (null === self::$_instance) {
            self::$_instance = new self($environment);
        }
        return self::$_instance;
    }

    /**
     *
     * Runs the application.
     * This is the main entrance of an application.
     * 
     */
    public function run() {
        $type = strtolower($this->config['returnType']);

        $request = Kant::$container->instance('Kant\Http\Request', Request::capture());

        $response = Kant::$container->instance('Kant\Http\Response', Response::create($request, Response::HTTP_OK, [
                    'Content-Type' => $this->outputType[$type]
        ]));

        $this->setCache($this->config['cache']);
        $this->setDb();

        $this->setCookie($this->config['cookie'], $request, $response);
        $this->setSession($this->config['session'], $request, $response);

        $data = $this->dispatch($this->route($request->path()));
        $result = $this->parseData($data, $type);

        $response->setContent($result)->send();
        $this->end();
    }

    /**
     * Parpare
     */
    protected function preInit($config) {
        //set default timezone
        if (isset($config['timezone'])) {
            $this->setTimeZone($config['timezone']);
        } elseif (!ini_get('date.timezone')) {
            $this->setTimeZone('UTC');
        }

        $this->setLanguage($config['language']);

        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $components['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $components['components'][$id] = $config['components'][$id];
                $components['components'][$id]['class'] = $component['class'];
            }
        }

        Component::__construct($components);
//
        if (!$config['enableDebugLogs']) {
            foreach (Kant::$app->getLog()->targets as $target) {
                $target->enabled = false;
            }
        }

        require_once APP_PATH . 'Bootstrap.php';
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
     * End
     */
    protected function end() {
        if (Factory::getConfig()->get('debug')) {
            Runtime::mark('end');
        }
    }

    /**
     * Route
     */
    protected function route($path) {
        //remove url suffix
        $pathinfo = str_replace(Factory::getConfig()->get('urlSuffix'), '', $path);

        Route::import(Factory::getConfig()->get('route'));
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
        $pathinfo = Factory::getPathInfo()->parsePathinfo();
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
    public function module($dispatcher) {
        KantRegistry::set('dispatcher', $dispatcher);
        $this->dispatcher = $dispatcher;

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
                throw new KantException(sprintf("No controller exists:%s", ucfirst($this->dispatcher[1]) . 'Controller'));
            }
        }


        //action name
        $action = $this->dispatcher[2] ?: ucfirst(Factory::getConfig()->get('route.act'));
        $data = $this->callClass($controller . "@" . $action . Factory::getConfig()->get('actionSuffix'));
        return $data;
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
            throw new \Exception(sprintf("File does not exists:%s", $filepath));
        }
        include $filepath;

        $namespace = "App\\{$module}\\Controller\\";
        $controller = $namespace . $controller;
        return $controller;
    }

    /**
     * Set the database connection component.
     */
    public function setDb() {
        $dbConfig = Factory::getConfig()->get('database');
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
     * Returns the log dispatcher component.
     * @return \yii\log\Dispatcher the log dispatcher application component.
     */
    public function getLog() {
        return $this->get('log');
    }

    /**
     * Returns the error handler component.
     * @return \Kant\ErrorHandler\ErrorHandler
     */
    public function getErrorHandler() {
        return $this->get('errorHandler');
    }

    /**
     * Returns the internationalization (i18n) component
     * @return \Kant\I18n\I18N the internationalization application component.
     */
    public function getI18n() {
        return $this->get('i18n');
    }

    public function getFiles() {
        return $this->get('files');
    }

    /**
     * Returns the request component.
     * @return Request the request component.
     */
    public function getRequest() {
        return $this->get('Kant\Http\Request');
    }

    /**
     * Returns the response component.
     * @return Response the response component.
     */
    public function getResponse() {
        return $this->get('response');
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
