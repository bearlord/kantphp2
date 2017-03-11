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
use Kant\Config\Config;
use Kant\Routing\Route;
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

    /**
     * The Laravel framework version.
     *
     * @var string
     */
    const VERSION = '2.2.0';

    private static $_instance = null;

    /**
     * Config object instance
     * @var type 
     */
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
     * Get the version number of the application.
     *
     * @return string
     */
    public function version() {
        return static::VERSION;
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
        return Factory::getConfig()->merge($appConfig);
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
    protected function setCookie($config, Request $request, Response $response) {
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
        $type = strtolower($this->config->get('returnType'));

        $request = $this->singleton('Kant\Http\Request', Request::capture());

        $response = $this->singleton('Kant\Http\Response', Response::create($request, Response::HTTP_OK, [
                    'Content-Type' => $this->outputType[$type]
        ]));

        $this->setCache($this->config->get('cache'));
        $this->setDb();

        $this->setCookie($this->config->get('cookie'), $request, $response);
        $this->setSession($this->config->get('session'), $request, $response);


        $router = Kant::createObject(\Kant\Routing\Router::class);

//        $route->group([], APP_PATH . 'Bootstrap.php');
        $router->group(['middleware' => 'web', 'namespace' => 'App\Http\Controller'], APP_PATH . 'Bootstrap.php');
//        var_dump($route->routes);
        $router->dispatch($request, $response);


//        $this->dispatch($this->route($request->path()), $type, $response);

        $response->send(); 
        $this->end();
    }

    /**
     * Parpare
     */
    protected function preInit(Config $config) {
        //set default timezone
        if ($config->get('timezone') != "") {
            $this->setTimeZone($config->get('timezone'));
        } elseif (!ini_get('date.timezone')) {
            $this->setTimeZone('UTC');
        }

        $this->setLanguage($config->get('language'));

        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config->get('components')[$id])) {
                $components['components'][$id] = $component;
            } elseif (is_array($config->get('components')[$id]) && !isset($config->get('components')[$id]['class'])) {
                $components['components'][$id] = $config->get('components')[$id];
                $components['components'][$id]['class'] = $component['class'];
            }
        }

        Component::__construct($components);
//
        if ($config->get('enableDebugLogs')) {
            foreach (Kant::$app->getLog()->targets as $target) {
                $target->enabled = false;
            }
        }
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

    public function getRouter() {
        return Kant::createObject(\Kant\Routing\Router::class);
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $class
     * @param  mixed   $instance
     * @return void
     */
    public function singleton($class, $instance) {
        return Kant::$container->singleton($class, $instance);
    }

}
