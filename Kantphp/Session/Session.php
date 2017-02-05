<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Session;

use Kant\Session\File\SessionFile;
use Kant\Session\Sqlite\SessionSqlite;
use Kant\Session\Mysql\SessionMysql;

final class Session {

    private static $_session;

    /**
     * Session List
     * @var type 
     */
    protected $sessionList = array();

    public function __construct() {
        
    }

    public static function platform($config = "") {
        $options = self::parseConfig($config);
        if (self::$_cache == '') {
            self::$_cache = (new self())->connect($options);
        }
        return self::$_cache;
    }

    public static function parseConfig($config = "") {
        if ($config == "") {
            $config = KantFactory::getConfig()->get('session.original');
        } elseif (is_string($config)) {
            $config = KantFactory::getConfig()->get('session.' . $config);
        }
        return $config;
    }

    /**
     * Get instantce of the final object
     * 
     * @param type $config
     */
    public static function getInstance($config) {
        $options = self::parseConfig($config);
        if (self::$_session == '') {
            self::$_session = (new self())->load($options);
        }
        return self::$_session;
    }

    public function load($options) {
        $type = ucfirst($options['type']);
        $class = "\\Kant\\Session\\Driver\\{$type}\\Session{$type}";
        $object = new $class($options);
        return $object;
    }

}

?>
