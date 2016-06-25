<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

use Kant\Kant;
use Kant\Config\KantConfig;
use Kant\Dispatch\Dispatch;
use Kant\Route\Route;
use Kant\Cache\Cache;
use Kant\Session\Session;
use Kant\Pathinfo\Pathinfo;

class KantFactory {

    /**
     * Global application object
     */
    public static $application = null;

    /**
     * Dispatch object
     * 
     */
    public static $dispatch = null;

    /**
     * Config object
     * 
     */
    public static $config = null;

    /**
     * Route object
     * 
     */
    public static $route = null;

    /**
     * Cache object
     * 
     */
    public static $cache = null;

    /**
     * Session object
     * 
     */
    public static $session = null;

    /**
     *
     * Pathinfo object
     */
    public static $pathinfo = null;

    /**
     * Get a application object.
     * 
     * Returns the global object, only creating it if it doesn't already exist.
     * 
     * @param string $env
     * @return object
     */
    public static function getApplication($env) {
        if (!self::$application) {
            self::$application = Kant::getInstance($env);
        }
        return self::$application;
    }

    /**
     * Get dispatch object
     */
    public static function getDispatch() {
        if (!self::$dispatch) {
            self::$dispatch = Dispatch::getInstance();
        }
        return self::$dispatch;
    }

    /**
     * Get config object
     */
    public static function getConfig() {
        if (!self::$config) {
            //Core configuration
            $coreConfig = include KANT_PATH . DIRECTORY_SEPARATOR . 'Config/Convention.php';
            self::$config = new KantConfig($coreConfig);
        }
        return self::$config;
    }

    /**
     * Get config object
     */
    public static function getRoute() {
        if (!self::$route) {
            self::$route = Route::getInstance();
        }
        return self::$route;
    }

    /**
     * Get cache object
     */
    public static function getCache($config) {
        if (!self::$cache) {
            self::$cache = Cache::getInstance($config);
        }
        return self::$cache;
    }

    /**
     * Get session object
     * 
     */
    public static function getSession() {
        if (!self::$session) {
            self::$session = Session::getInstance();
        }
        return self::$session;
    }

    /**
     * Get Pathinfo object
     */
    public static function getPathInfo() {
        if (!self::$pathinfo) {
            self::$pathinfo = Pathinfo::getInstance();
        }
        return self::$pathinfo;
    }

}
