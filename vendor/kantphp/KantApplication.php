<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

use Kant\Foundation\Component;
use Kant\Foundation\Module;
use Kant\Helper\ArrayHelper;
use Kant\Config\Config;
use Kant\Http\Request;
use Kant\Http\Response;
use Kant\Routing\Router;
use Kant\Runtime\Runtime;
use Kant\Exception\KantException;
use ReflectionMethod;

/**
 * Application is the base class for all application classes.
 * 
 * @property \Kant\View\AssetManager $assetManager The asset manager application component. This property is read-only.
 * @property \Kant\Cache\Cache $cache The cache application component. Null if the component is not enabled. This property is read-only.
 * @property \Kant\Config\Config $config The config application component. This property is read-only.
 * @property \Kant\Database\Connection $db The database connection. This property is read-only.
 * @property \Kant\Filesystem\Filesystem $formatter The formatter application component. This property is read-only.
 * @property \Kant\I18n\Formatter $formatter The formatter application component. This property is read-only.
 * @property \Kant\I18n\I18N $i18n The internationalization application component. This property is read-only.
 * @property \Kant\Log\Dispatcher $log The log dispatcher application component. This property is read-only.
 * @property \Kant\Routing\Redirector $redirect The request component. This property is read-only.
 * @property \Kant\Http\Request $request The request component. This property is read-only.
 * @property \Kant\Http\Response $response The response component. This property is read-only.
 * @property \Kant\Routing\Router $router The router component. This property is read-only.
 * @property \Kant\Session\Session $session The session component. This property is read-only.
 * @property \Kant\Foundation\Security $security The security component. This property is read-only.
 * @property \Kant\Filesystem\FilesystemManager $store The store component. This property is read-only.
 * @property string $timeZone The time zone used by this application.
 * @property \Kant\View\View $view The view application component that is used to render various view files. This property is read-only. 
 * 
 */
class KantApplication extends Module {

    /**
     * @var string the charset currently used for the application.
     */
    public $charset = 'UTF-8';
    private static $_instance = null;

    /**
     * Config object instance
     * @var object 
     */
    public $config;

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
    public $sourceLanguage = 'en-US';
    private $_runtimePath;
    private $_homeUrl;

    /**
     * Dispathc info
     *
     * @var array
     */
    public $dispatcher = null;

    /**
     * Constructs
     * Initialize Config,register Cache,Database,Session,Cookie
     * 
     * @param string $env
     */
    public function __construct($env) {
        Kant::$app = $this;
        $this->config = $config = $this->initConfig($env);
        $this->preInit($config);
    }

    /**
     * @inheritdoc
     */
    public function init() {
        $this->setRequest();
        $this->bootstrap();
    }

    /**
     * Initializes extensions and executes bootstrap components.
     * This method is called by [[init()]] after the application has been fully configured.
     * If you override this method, make sure you also call the parent implementation.
     */
    public function bootstrap() {
        $format = strtolower($this->config->get('responseFormat'));
        $request = $this->getRequest();
        $this->setResponse($request);
        Kant::setAlias('@webroot', dirname($request->getScriptName()));
        Kant::setAlias('@web', $request->getBaseUrl());
    }

    /**
     * Returns the configuration of core application components.
     * @see set()
     */
    public function coreComponents() {
        return [
            'log' => ['class' => 'Kant\Log\Dispatcher'],
            'i18n' => ['class' => 'Kant\I18n\I18N'],
            'formatter' => ['class' => 'Kant\I18n\Formatter'],
            'assetManager' => ['class' => 'Kant\View\AssetManager'],
            'security' => ['class' => 'Kant\Foundation\Security'],
            'store' => ['class' => 'Kant\Filesystem\FilesystemManager'],
            'files' => ['class' => 'Kant\Filesystem\Filesystem'],
            'redirect' => ['class' => 'Kant\Routing\Redirector'],
            'user' => ['class' => 'Kant\Identity\User'],
            'manager' => ['class' => 'Kant\Identity\User'],
        ];
    }

    /**
     * Init Config
     */
    protected function initConfig($env) {
        $appConfig = ArrayHelper::merge(
                        require KANT_PATH . DIRECTORY_SEPARATOR . 'Config/Convention.php', require CFG_PATH . $env . DIRECTORY_SEPARATOR . 'Config.php', [
                    'environment' => $env,
                    'config_path' => CFG_PATH . $env . DIRECTORY_SEPARATOR
                        ]
        );
        return $this->getConfig()->merge($appConfig);
    }

    /**
     * Register Request
     */
    public function setRequest() {
        Kant::$container->set('Kant\Http\Request', Request::capture());
    }

    /**
     * Register Response
     * 
     * @param Request $request
     * @param type $format
     */
    public function setResponse(Request $request) {
        Kant::$container->set('Kant\Http\Response', Response::create($request, Response::HTTP_OK));
    }

    /**
     * Initialize session
     * 
     * @staticvar type $session
     * @return type
     */
    protected function setSession($config, $request, $response) {
        Kant::$container->set('Kant\Session\Session', Kant::createObject([
                    'class' => \Kant\Session\StartSession::class], [$config, $request, $response]
                )->handle());
    }

    /**
     * Register Cookie
     */
    protected function setCookie($config, Request $request, Response $response) {
        $this->set('cookie', Kant::createObject([
                    'class' => \Kant\Cookie\Cookie::class], [$config, $request, $response]
        ));
    }

    /**
     * Set the view Object
     */
    public function setView() {
        Kant::$container->set('Kant\View\View', Kant::createObject('Kant\View\View'));
    }

    /**
     * Register cache
     * 
     * @param type $config
     * @return null
     */
    protected function setCache($config) {
        return $this->set('cache', Kant::createObject([
                            'class' => \Kant\Cache\Cache::class], [$config]
                        )->handle());
    }

    /**
     * Set the database connection component.
     */
    public function setDb($config) {
        foreach ($config as $key => $config) {
            $this->set($key, array_merge([
                'class' => 'Kant\Database\Connection'
                            ], $config));
        }
    }

    /**
     * Returns the request component.
     * @return Request the request component.
     */
    public function getRequest() {
        return Kant::$container->get('Kant\Http\Request');
    }

    /**
     * Returns the response component.
     * @return Response the response component.
     */
    public function getResponse() {
        return Kant::$container->get('Kant\Http\Response');
    }

    /**
     * Get Session instance
     * 
     * @return object
     */
    public function getSession() {
        return Kant::$container->get('Kant\Session\Session');
    }

    /**
     * Returns the view object.
     * @return View|\Kant\View\View the view application component that is used to render various view files.
     */
    public function getView() {
        return Kant::$container->get('Kant\View\View');
    }

    /**
     * Returns the formatter component.
     * @return \Kant\I18n\Formatter the formatter application component.
     */
    public function getFormatter() {
        return Kant::$container->get('Kant\I18n\Formatter');
    }

    /**
     * Get Cookie instance
     * @return object
     */
    public function getCookie() {
        return $this->get('cookie');
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
     * Returns the database connection component.
     * @return \Kant\Database\Connection the database connection.
     */
    public function getDb() {
        return $this->get('db');
    }

    /**
     * Returns the log dispatcher component.
     * @return \Kant\Log\Dispatcher the log dispatcher application component.
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

    /**
     * Returns the files component
     * @return Kant\Filesystem\Filesystem
     */
    public function getFiles() {
        return $this->get('files');
    }

    /**
     * Returns the asset manager.
     * @return \Kant\View\AssetManager the asset manager application component.
     */
    public function getAssetManager() {
        return $this->get('assetManager');
    }

    /**
     * Returns the security component.
     * @return \Kant\Foundation\Security the security application component.
     */
    public function getSecurity() {
        return $this->get('security');
    }

    public function getStore() {
        return $this->get('store');
    }
    
    
    /**
     * Returns the user component.
     * @return User the user component.
     */
    public function getManager() {
        return $this->get('manager');
    }

    /**
     * Get Configure instance
     */
    public function getConfig() {
        return Kant::createObject('Kant\Config\Config');
    }

    /**
     * Returns the router component
     * @return type
     */
    public function getRouter() {
        return Kant::createObject('Kant\Routing\Router');
    }

    /**
     * @return string the homepage URL
     */
    public function getHomeUrl()
    {
        if ($this->_homeUrl === null) {
            return $this->getRequest()->getBaseUrl() . '/';
        } else {
            return $this->_homeUrl;
        }
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
        $request = $this->getRequest();

        $response = $this->getResponse();

        $router = $this->getRouter();

        $this->setCache($this->config->get('cache'));
        $this->setDb($this->config->get('database'));

        $this->setCookie($this->config->get('cookie'), $request, $response);
        $this->setSession($this->config->get('session'), $request, $response);

        $this->setView();
        $router->dispatch($request, $response);
        
        $this->getSession()->save();
        $response->send();
        $this->end();
    }

    /**
     * Returns the directory that stores runtime files.
     * @return string the directory that stores runtime files.
     * Defaults to the "runtime" subdirectory under [[basePath]].
     */
    public function getRuntimePath() {
        if ($this->_runtimePath === null) {
            $this->setRuntimePath(APP_PATH . 'Runtime');
        }

        return $this->_runtimePath;
    }

    /**
     * Sets the directory that stores runtime files.
     * @param string $path the directory that stores runtime files.
     */
    public function setRuntimePath($path) {
        $this->_runtimePath = $path;
    }

    /**
     * Parpare
     */
    protected function preInit(Config $config) {
        if ($config->get('vendorPath') != "") {
            $this->setVendorPath($config->get('vendorPath'));
        } else {
            // set "@vendor"
            $this->getVendorPath();
        }

        //set default timezone
        if ($config->get('timezone') != "") {
            $this->setTimeZone($config->get('timezone'));
        } elseif (!ini_get('date.timezone')) {
            $this->setTimeZone('UTC');
        }

        $this->setLanguage($config->get('language'));

        // merge core components with custom components
        $componentsKeys = array_merge(array_keys($this->coreComponents()), array_keys($config->get('components')));
        foreach ($componentsKeys as $id) {
             if (!isset($config->get('components')[$id])) {
                $components['components'][$id] = $this->coreComponents()[$id];
            } else {
                $components['components'][$id] = $config->get('components')[$id];
                 if (is_array($config->get('components')[$id]) && !isset($config->get('components')[$id]['class'])) {
                    $components['components'][$id]['class'] = $this->coreComponents()[$id]['class'];
                }
            } 
        }
        Component::__construct($components);
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
        if (Kant::$app->config->get('debug')) {
            Runtime::mark('end');
        }
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $class
     * @param  mixed   $instance
     * @return void
     */
    public function singleton($class, $instance) {
        $this->set($class, $instance);
        return $this->get($class);
    }

    /**
     * Init Module Config;
     * @param type $module
     */
    public function setModuleConfig($module) {
        $configFilePath = MODULE_PATH . $module . DIRECTORY_SEPARATOR . 'Config.php';
        if (file_exists($configFilePath)) {
            $this->config->merge(require $configFilePath);
        }
        $this->getResponse()->format = $this->config->get('responseFormat');
    }

    /**
     * Set view dispatcher
     * 
     * @param array $dispatcher
     */
    public function setDispatcher($dispatcher) {
        $this->dispatcher = implode("/", $dispatcher);
        $this->getView()->setDispatcher($dispatcher);
    }

    /**
     * Register the route middleware
     * 
     * @param object $config
     * @param Router $router
     */
    public function setRouteMiddleware($config, Router $router) {
        $routeMiddleware = $config->get('routeMiddleware');
        foreach ($routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }
    }

}
