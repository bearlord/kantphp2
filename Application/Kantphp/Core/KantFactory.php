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
            self::$dispatch = KantDispatch::getInstance();
        }
        return self::$dispatch;
    }

    /**
     * Get Config Object
     */
    public static function getConfig() {
        if (!self::$config) {
            //Core configuration
            $coreConfig = include KANT_PATH . DIRECTORY_SEPARATOR . 'Config/Base.php';            
            self::$config = new KantConfig($coreConfig);
        }
        return self::$config;
    }

}
