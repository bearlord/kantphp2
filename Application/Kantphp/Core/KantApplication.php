<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

use Kant\Log\Log;
use Kant\Hook\Hook;
use Kant\Runtime\Runtime;
use ReflectionException;
use ReflectionMethod;

!defined('IN_KANT') && exit('Access Denied');

require_once KANT_PATH . '/Core/Base.php';

final class Kant {

    private static $_instance = null;
    private static $_autoCoreClass = array(
        'Kant\KantRouter' => 'Core/KantRouter',
        'Kant\KantDispatch' => 'Core/KantDispatch',
        'Kant\KantConfig' => 'Config/KantConfig',
        'Kant\KantRegistry' => 'Core/KantRegistry',
        'Kant\KantException' => 'Core/KantException'
    );
    private static $_environment = 'Development';

    /**
     * Run time config
     *
     * @var Kant_Config
     */
    public static $config;

    /**
     * Run time config's reference, for better performance
     *
     * @var array
     */
    protected static $_config;

    /**
     * Object register
     *
     * @var array
     */
    protected static $_reg = array();

    /**
     * Router
     *
     * @var Kant_Router
     */
    protected $_router;

    /**
     * Path info
     *
     * @var string
     */
    private $_pathInfo = null;

    /**
     * Dispathc info
     *
     * @var array
     */
    protected $_dispatchInfo = null;
    protected $defaultAction = 'Index';

    /**
     * Constructs
     */
    public function __construct() {
        $_config['class'] = self::$_autoCoreClass;
        self::$config = new Config\KantConfig($_config);
        //Core configuration
        $coreConfig = include KANT_PATH . DIRECTORY_SEPARATOR . 'Config/Base.php';
        //Application configration
        $appConfig = include CFG_PATH . self::$_environment . DIRECTORY_SEPARATOR . 'Config.php';
        self::$config->merge($coreConfig)->merge($appConfig);
        self::$_config = self::$config->reference();
        KantRegistry::set('config', self::$_config);
    }

    /**
     * Create application
     * 
     * @param type $environment
     * @return type
     */
    public static function createApplication($environment = '') {
        if ($environment == NULL) {
            $environment = self::$_environment;
        }
        return self::getInstance($environment);
    }

    /**
     * Singleton instance
     * 
     * @param type $environment
     * @return type
     */
    public static function getInstance($environment = 'Development') {
        self::registerAutoload();
        self::$_environment = $environment;
        KantRegistry::set('environment', $environment);
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Boot
     * 
     */
    public function boot() {
        //default timezone
        date_default_timezone_set(self::$_config['default_timezone']);
        //logfile initialization
        Log::init();
        Hook::import(self::$_config['tags']);
        if (self::$_config['debug']) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            Runtime::mark('begin');
        }
        Hook::listen('app_begin');
        $this->exec();
        Hook::listen('app_end');
        if (self::$_config['debug']) {
            Runtime::mark('end');
        }
    }

    /**
     * Execution
     * 
     * @throws KantException
     * @throws ReflectionException
     */
    public function exec() {
        $this->_dispatchInfo = KantDispatch::getInstance()->getDispatchInfo();
        $this->bootstrap();
        $controller = $this->controller();
        if (!$controller) {
            $controller = $this->controller('empty');
            if (empty($controller)) {
                throw new KantException(sprintf("No controller exists:%s", ucfirst($this->_dispatchInfo['ctrl']) . 'Controller'));
            }
        }
        $action = isset($this->_dispatchInfo['act']) ? $this->_dispatchInfo['act'] . self::$_config['action_suffix'] : 'Index' . self::$_config['action_suffix'];
        try {
            if (!preg_match('/^[A-Za-z](\w)*$/', $action)) {
                throw new ReflectionException();
            }
            $method = new ReflectionMethod($controller, $action);
            if ($method->isPublic() && !$method->isStatic()) {
                $method->invoke($controller);
            } else {
                throw new ReflectionException();
            }
        } catch (ReflectionException $e) {
            $method = new ReflectionMethod($controller, '__call');
            $method->invokeArgs($controller, array($action, ''));
        }
    }

    /**
     * 
     * @staticvar array $classes
     * @return boolean|array|\classname
     * @throws KantException
     */
    protected function controller($controller = '') {
        $module = isset($this->_dispatchInfo['module']) ? ucfirst($this->_dispatchInfo['module']) : '';
        if (empty($module)) {
            throw new KantException('No Module found');
        }
        if (empty($controller)) {
            $controller = ucfirst($this->_dispatchInfo['ctrl']) . 'Controller';
        } else {
            $controller = ucfirst($controller) . "Controller";
        }
        $filepath = APP_PATH . "Module/$module/Controller/$controller.php";
        if (file_exists($filepath)) {
            include $filepath;
            $namespace = "$module\\Controller\\";
            $controller = $namespace . $controller;
            if (class_exists($controller)) {
                $class = new $controller;
                return $class;
            }
        }
    }

    /**
     * Bootstrap
     * 
     * @staticvar array $classes
     * @return boolean|array
     */
    protected function bootstrap() {
        $classname = "Bootstrap";
        $filepath = APP_PATH . "Bootstrap/$classname.php";
        if (file_exists($filepath)) {
            include $filepath;
            $class = "Bootstrap\\$classname";
            if (method_exists($class, 'initialize')) {
                return call_user_func(array($class, 'initialize'));
            }
        }
    }

    /**
     * Load core class
     * 
     * @param type $className
     * @param type $dir
     * @return boolean
     */
    public static function autoload($className, $dir = '') {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }
        try {
            if (in_array($className, array_keys(self::$_autoCoreClass)) == true) {
                $filename = KANT_PATH . self::$_autoCoreClass[$className] . ".php";
            } else {
                if (strpos($className, "\\") !== false) {
                    if (strpos($className, "Kant") === 0) {
                        $className = str_replace('Kant\\', '', $className) . ".php";
                        $filename = KANT_PATH . $className;
                    } else {
                        $className = str_replace('\\', '/', $className) . ".php";

                        $filename = APP_PATH . $className;
                    }
                } else {
                    $filename = $className;
                }
            }
            require_once $filename;
        } catch (RuntimeException $e) {
            exit('Require File Error: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * Register autoload function
     *
     * @param string $func
     * @param boolean $enable
     */
    public static function registerAutoload($func = 'self::autoload', $enable = true) {
        $enable ? spl_autoload_register($func) : spl_autoload_unregister($func);
    }

}
