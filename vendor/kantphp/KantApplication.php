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
use Kant\Runtime\Runtime;
use Kant\Exception\KantException;
use ReflectionMethod;

class KantApplication extends Module {

    /**
     * @var string the charset currently used for the application.
     */
    public $charset = 'UTF-8';
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
    private $_runtimePath;

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
        $type = strtolower($this->config->get('returnType'));
        $request = $this->getRequest();
        $this->setResponse($request, $type);
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
            'store' => ['class' => 'Kant\Filesystem\FilesystemManager'],
            'files' => ['class' => 'Kant\Filesystem\Filesystem']
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
     * @param type $type
     */
    public function setResponse(Request $request, $type) {
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

    public function getFilesManager(){
        return $this->get('filesManager');
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

        $this->setCache($this->config->get('cache'));
        $this->setDb($this->config->get('database'));

        $this->setCookie($this->config->get('cookie'), $request, $response);
        $this->setSession($this->config->get('session'), $request, $response);

        $this->setView();
        $this->getRouter()->dispatch($request, $response);

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
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config->get('components')[$id])) {
                $components['components'][$id] = $component;
            } elseif (is_array($config->get('components')[$id]) && !isset($config->get('components')[$id]['class'])) {
                $components['components'][$id] = $config->get('components')[$id];
                $components['components'][$id]['class'] = $component['class'];
            }
        }

        Component::__construct($components);

        if ($config->get('enableDebugLogs')) {
            foreach (Kant::$app->getLog()->targets as $target) {
                $target->enabled = true;
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
        if (Kant::$app->config->get('debug')) {
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

}
