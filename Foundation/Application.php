<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Foundation;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Foundation\Module;
use Kant\Helper\ArrayHelper;
use Kant\Config\Config;
use Kant\Http\Request;
use Kant\Http\Response;
use Kant\Routing\Router;
use Kant\Runtime\Runtime;
use Kant\Exception\InvalidConfigException;
use Kant\Exception\ExitException;
use ReflectionMethod;

/**
 * Application is the base class for all application classes.
 *
 * @property \Kant\View\AssetManager $assetManager The asset manager application component. This property is read-only.
 * @property \Kant\Caching\Cache $cache The cache application component. Null if the component is not enabled. This property is read-only.
 * @property \Kant\Cookie\Cookie $cookie The cookie application component. Null if the component is not enabled. This property is read-only.
 * @property \Kant\Config\Config $config The config application component. This property is read-only.
 * @property \Kant\Database\Connection $db The database connection. This property is read-only.
 * @property \Kant\Filesystem\Filesystem $files The filesystem component. This property is read-only.
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
abstract class Application extends Module
{

	/**
	 * @event Event an event raised before the application starts to handle a request.
	 */
	const EVENT_BEFORE_REQUEST = 'beforeRequest';

	/**
	 * @event Event an event raised after the application successfully handles a request (before the response is sent out).
	 */
	const EVENT_AFTER_REQUEST = 'afterRequest';

	/**
	 * Application state used by [[state]]: application just started.
	 */
	const STATE_BEGIN = 0;

	/**
	 * Application state used by [[state]]: application is initializing.
	 */
	const STATE_INIT = 1;

	/**
	 * Application state used by [[state]]: application is triggering [[EVENT_BEFORE_REQUEST]].
	 */
	const STATE_BEFORE_REQUEST = 2;

	/**
	 * Application state used by [[state]]: application is handling the request.
	 */
	const STATE_HANDLING_REQUEST = 3;

	/**
	 * Application state used by [[state]]: application is triggering [[EVENT_AFTER_REQUEST]]..
	 */
	const STATE_AFTER_REQUEST = 4;

	/**
	 * Application state used by [[state]]: application is about to send response.
	 */
	const STATE_SENDING_RESPONSE = 5;

	/**
	 * Application state used by [[state]]: application has ended.
	 */
	const STATE_END = 6;

	/**
	 *
	 * @var string the charset currently used for the application.
	 */
	public $charset = 'UTF-8';
	private static $_instance = null;

	/**
	 * Config object instance
	 *
	 * @var object
	 */
	public $config;

	/**
	 *
	 * @var string the language that is meant to be used for end users. It is recommended that you
	 *      use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
	 *      for English, while `en-US` stands for English (United States).
	 * @see sourceLanguage
	 */
	public $language = 'zh-CN';

	/**
	 *
	 * @var string the language that the application is written in. This mainly refers to
	 *      the language that the messages and view files are written in.
	 * @see language
	 */
	public $sourceLanguage = 'en-US';

	/**
	 * Dispatcher type
	 * @string type
	 */
	public $dispatcherType = null;

	/**
	 * Dispatcher info
	 *
	 * @var array
	 */
	public $dispatcher = null;

	/**
	 *
	 * @var Controller the currently active controller instance
	 */
	public $controller;
	
	/**
     * @var int the current application state during a request handling life cycle.
     * This property is managed by the application. Do not modify this property.
     */
    public $state;

	/**
	 * Constructs
	 * Initialize Config,register Cache,Database,Session,Cookie
	 *
	 * @param string $env
	 */
	public function __construct($config)
	{
		Kant::$app = $this;
		
		$this->state = self::STATE_BEGIN;
		
		$this->config = $this->initConfig($config);

		$this->preInit($this->config);

        $this->registerErrorHandler($this->config);

        $componets['components'] = $this->config->get('components');

        Component::__construct($this->config->reference());


	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->setRequest();
		$this->bootstrap();
	}

	/**
	 * Initializes extensions and executes bootstrap components.
	 * This method is called by [[init()]] after the application has been fully configured.
	 * If you override this method, make sure you also call the parent implementation.
	 */
	protected function bootstrap()
	{
		$request = $this->getRequest();
		$this->setResponse($request);
	}

    /**
     * Registers the errorHandler component as a PHP error handler.
     * @param array $config application config
     */
    protected function registerErrorHandler($config)
    {
        if (KANT_ENABLE_ERROR_HANDLER) {
            /*
            if (!isset($config->components['errorHandler']['class'])) {
                echo "Error: no errorHandler component is configured.\n";
                exit(1);
            }
            $this->set('errorHandler', $config->components['errorHandler']);
            */
            $this->set('errorHandler', Kant::createObject([
                'class' => \Kant\Web\ErrorHandler::class
            ]));
            $this->getErrorHandler()->register();
        }
    }

	/**
	 * Returns the configuration of core application components.
	 *
	 * @see set()
	 */
	public function coreComponents()
	{
		return [
			'log' => [
				'class' => 'Kant\Log\Dispatcher'
			],
			'i18n' => [
				'class' => 'Kant\I18n\I18N'
			],
			'formatter' => [
				'class' => 'Kant\I18n\Formatter'
			],
			'assetManager' => [
				'class' => 'Kant\View\AssetManager'
			],
			'security' => [
				'class' => 'Kant\Foundation\Security'
			],
			'store' => [
				'class' => 'Kant\Filesystem\FilesystemManager'
			],
			'files' => [
				'class' => 'Kant\Filesystem\Filesystem'
			],
			'redirect' => [
				'class' => 'Kant\Routing\Redirector'
			],
			'router' => [
				'class' => 'Kant\Routing\Router'
			],
			'user' => [
				'class' => 'Kant\Identity\User'
			],
			'view' => [
				'class' => 'Kant\View\View'
			],
			'user' => [
				'class' => 'Kant\Identity\User'
			],
		];
	}

	/**
	 * Init Config
	 */
	protected function initConfig($config)
	{
		$appConfig = ArrayHelper::merge(
						require KANT_PATH . DIRECTORY_SEPARATOR . 'Config/Convention.php', $config
		);
		return $this->getConfig()->merge($appConfig);
	}

	/**
	 * Register Request
	 */
	public function setRequest()
	{
		Kant::$container->set(Request::class, Request::capture());
	}

	/**
	 * Register Response
	 *
	 * @param Request $request
	 * @param type $format
	 */
	public function setResponse(Request $request)
	{
		Kant::$container->set(Response::class, Response::create($request, Response::HTTP_OK));
	}

	/**
	 * Initialize session
	 *
	 * @staticvar type $session
	 * @return type
	 */
	protected function setSession($config)
	{
		Kant::$container->set('Kant\Session\Session', Kant::createObject([
					'class' => \Kant\Session\StartSession::class
						], [
					$config,
				])->handle());
	}

	/**
	 * Register Cookie
	 */
	protected function setCookie($config)
	{
		$this->set('cookie', Kant::createObject([
					'class' => \Kant\Cookie\Cookie::class
						], [
					$config
		]));
	}

	/**
	 * Returns the request component.
	 *
	 * @return Request the request component.
	 */
	public function getRequest()
	{
		return Kant::$container->get(Request::class);
	}

	/**
	 * Returns the response component.
	 *
	 * @return Response the response component.
	 */
	public function getResponse()
	{
		return Kant::$container->get(Response::class);
	}

	/**
	 * Get Session instance
	 *
	 * @return object
	 */
	public function getSession()
	{
		return Kant::$container->get('Kant\Session\Session');
	}

	/**
	 * Returns the view object.
	 *
	 * @return View|\Kant\View\View the view application component that is used to render various view files.
	 */
	public function getView()
	{
		return $this->get('view');
	}

	/**
	 * Returns the formatter component.
	 *
	 * @return \Kant\I18n\Formatter the formatter application component.
	 */
	public function getFormatter()
	{
		return $this->get('formatter');
	}

	/**
	 * Get Cookie instance
	 *
	 * @return object
	 */
	public function getCookie()
	{
		return $this->get('cookie');
	}

	/**
	 * Get cache instance
	 *
	 * @return object
	 */
	public function getCache()
	{
		return $this->get('cache');
	}

	/**
	 * Returns the database connection component.
	 *
	 * @return \Kant\Database\Connection the database connection.
	 */
	public function getDb()
	{
		return $this->get('db');
	}

	/**
	 * Returns the log dispatcher component.
	 *
	 * @return \Kant\Log\Dispatcher the log dispatcher application component.
	 */
	public function getLog()
	{
		return $this->get('log');
	}

	/**
	 * Returns the error handler component.
	 *
	 * @return \Kant\ErrorHandler\ErrorHandler
	 */
	public function getErrorHandler()
	{
		return $this->get('errorHandler');
	}

	/**
	 * Returns the internationalization (i18n) component
	 *
	 * @return \Kant\I18n\I18N the internationalization application component.
	 */
	public function getI18n()
	{
		return $this->get('i18n');
	}

	/**
	 * Returns the files component
	 *
	 * @return Kant\Filesystem\Filesystem
	 */
	public function getFiles()
	{
		return $this->get('files');
	}

	/**
	 * Returns the asset manager.
	 *
	 * @return \Kant\View\AssetManager the asset manager application component.
	 */
	public function getAssetManager()
	{
		return $this->get('assetManager');
	}

	/**
	 * Returns the security component.
	 *
	 * @return \Kant\Foundation\Security the security application component.
	 */
	public function getSecurity()
	{
		return $this->get('security');
	}

	public function getStore()
	{
		return $this->get('store');
	}

	/**
	 * Get Configure instance
	 */
	public function getConfig()
	{
		return Kant::createObject('Kant\Config\Config');
	}

	/**
	 * Runs the application.
	 * This is the main entrance of an application.
	 */
	public function run()
	{
		try {

			$this->state = self::STATE_BEFORE_REQUEST;
			$this->trigger(self::EVENT_BEFORE_REQUEST);

			$this->state = self::STATE_HANDLING_REQUEST;

			$request = $this->getRequest();

			$response = $this->handleRequest($request);
			$this->state = self::STATE_AFTER_REQUEST;
			$this->trigger(self::EVENT_AFTER_REQUEST);

			$this->state = self::STATE_SENDING_RESPONSE;
			$this->end($response);

			$this->state = self::STATE_END;

			return $response->exitStatus;
		} catch (ExitException $e) {
			return $e->statusCode;
		}
	}

    /**
     * Handles the specified request.
     *
     * This method should return an instance of [[Response]] or its child class
     * which represents the handling result of the request.
     *
     * @param Request $request the request to be handled
     * @return Response the resulting response
     */
    abstract public function handleRequest($request);

    private $_runtimePath;

	/**
	 * Returns the directory that stores runtime files.
	 *
	 * @return string the directory that stores runtime files.
	 *         Defaults to the "runtime" subdirectory under [[basePath]].
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath === null) {
			$this->setRuntimePath(APP_PATH . DIRECTORY_SEPARATOR . 'runtime');
		}

		return $this->_runtimePath;
	}

	/**
	 * Sets the directory that stores runtime files.
	 *
	 * @param string $path
	 *            the directory that stores runtime files.
	 */
	public function setRuntimePath($path)
	{
		$this->_runtimePath = $path;
		Kant::setAlias('@runtime', $this->_runtimePath);
		Kant::setAlias('@session_path', $this->_runtimePath . DIRECTORY_SEPARATOR . 'sessions');
		Kant::setAlias('@log_path', $this->_runtimePath . DIRECTORY_SEPARATOR . 'logs');
		Kant::setAlias('@cache_path', $this->_runtimePath . DIRECTORY_SEPARATOR . 'caches');
	}

    private $_vendorPath;

    /**
     * Returns the directory that stores vendor files.
     *
     * @return string the directory that stores vendor files.
     *         Defaults to "vendor" directory under [[basePath]].
     */
    public function getVendorPath()
    {
        if ($this->_vendorPath === null) {
            $this->setVendorPath(dirname($this->getBasePath()) . '/vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     *
     * @param string $path
     *            the directory that stores vendor files.
     */
    public function setVendorPath($path)
    {
        $this->_vendorPath = Kant::getAlias($path);
        Kant::setAlias('@vendor', $this->_vendorPath);
        Kant::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower');
    }

	/**
	 * Parpare
	 */
	protected function preInit(Config $config)
	{
		if ($config->get('basePath') != '') {
			$this->setBasePath($config->get('basePath'));
		} else {
			throw new InvalidConfigException('The "basePath" configuration for the Application is required.');
		}

		if ($config->get('vendorPath') != "") {
			$this->setVendorPath($config->get('vendorPath'));
		} else {
			// set "@vendor"
			$this->getVendorPath();
		}

		if ($config->get('runtimePath') != "") {
			$this->setRuntimePath($config->get('runtimePath'));
		} else {
			// set "@runtime"
			$this->getRuntimePath();
		}

		// set default timezone
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
		$this->config->set('components', $components['components']);
//        Component::__construct($components);
	}

	/**
	 * Sets the root directory of the application and the @app alias.
	 * This method can only be invoked at the beginning of the constructor.
	 * @param string $path the root directory of the application.
	 * @property string the root directory of the application.
	 * @throws InvalidParamException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		parent::setBasePath($path);
		Kant::setAlias('@app', $this->getBasePath());
		Kant::setAlias('@tpl_path', $this->getBasePath() . DIRECTORY_SEPARATOR . 'view');
		Kant::setAlias('@lib_path', $this->getBasePath() . DIRECTORY_SEPARATOR . 'libary');
	}

	/**
	 * Returns the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_get().
	 * If time zone is not configured in php.ini or application config,
	 * it will be set to UTC by default.
	 *
	 * @return string the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-get.php
	 */
	public function getTimeZone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Sets the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_set().
	 * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
	 *
	 * @param string $value
	 *            the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-set.php
	 */
	public function setTimeZone($value)
	{
		date_default_timezone_set($value);
	}

	/**
	 * set the language that is meant to be used for end users.
	 * It is recommended that you
	 * use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
	 * for English, while `en-US` stands for English (United States).
	 */
	public function setLanguage($value)
	{
		$this->language = $value;
	}

	/**
	 * get the language that is meant to be used for end users.
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
     * Terminates the application.
     * This method replaces the `exit()` function by ensuring the application life cycle is completed
     * before terminating the application.
	 * 
     * @param Response $response the response to be sent. If not set, the default application [[response]] component will be used.
     * @throws ExitException if the application is in testing mode
     */
	public function end($response = null)
	{
		$this->getSession()->save();
		
		if ($this->state === self::STATE_BEFORE_REQUEST || $this->state === self::STATE_HANDLING_REQUEST) {
            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(self::EVENT_AFTER_REQUEST);
        }

        if ($this->state !== self::STATE_SENDING_RESPONSE && $this->state !== self::STATE_END) {
            $this->state = self::STATE_END;
        }
		
		$response = $response ? : $this->getResponse();
		$response->send();
		exit(0);
	}

	/**
	 * Register an existing instance as shared in the container.
	 *
	 * @param string $class
	 * @param mixed $instance
	 * @return void
	 */
	public function singleton($class, $instance)
	{
		$this->set($class, $instance);
		return $this->get($class);
	}

	/**
	 * Register the route middleware
	 *
	 * @param object $config
	 * @param Router $router
	 */
	public function setRouteMiddleware($config, Router $router)
	{
		$routeMiddleware = $config->get('routeMiddleware');
		foreach ($routeMiddleware as $key => $middleware) {
			$router->aliasMiddleware($key, $middleware);
		}
	}

}
