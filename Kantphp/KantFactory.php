<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

use Kant\Config\Config;
use Kant\Route\Route;
use Kant\Cache\Cache;
use Kant\Session\Session;
use Kant\Cookie\Cookie;
use Kant\Database\Connection;
use Kant\Pathinfo\Pathinfo;

class KantFactory {

    /**
     * Object container
     * 
     */
    public static $container = [
        'application' => '',
        'config' => '',
        'route' => '',
        'session' => '',
        'cookie' => '',
        'db' => '',
        'pathinfo' => ''
    ];

    /**
     * Get config object
     */
    public static function getConfig() {
        if (!self::$container['config']) {
            self::$container['config'] = new Config();
        }
        return self::$container['config'];
    }

    /**
     * Get config object
     */
    public static function getRoute() {
        if (!self::$container['route']) {
            self::$container['route'] = Route::getInstance();
        }
        return self::$container['route'];
    }

    /**
     * Get session object
     * 
     */
    public static function getSession($config) {
        if (!self::$container['session']) {
            self::$container['session'] = Session::getInstance($config);
        }
        return self::$container['session'];
    }

    /**
     * Get cookie object
     */
    public static function getCookie() {
        if (!self::$container['cookie']) {
            $config = self::getConfig()->get('cookie');
            self::$container['cookie'] = Cookie::getInstance($config);
        }
        return self::$container['cookie'];
    }

    public static function getDb() {
        if (!self::$container['db']) {
            $config = self::getConfig()->get("database.default");
            self::$container['db'] = new Connection($config);
        }
        return self::$container['db'];
    }

    /**
     * Get Pathinfo object
     */
    public static function getPathInfo() {
        if (!self::$container['pathinfo']) {
            self::$container['pathinfo'] = Pathinfo::getInstance();
        }
        return self::$container['pathinfo'];
    }

}
