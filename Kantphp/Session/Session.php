<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Session;

final class Session {

    private static $_session;

    /**
     * Session List
     * @var type 
     */
    protected $sessionList = array();

    public function __construct() {
        
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

    public static function parseConfig($config = "") {
        if ($config == "") {
            $config = KantFactory::getConfig()->get('session.original');
        } elseif (is_string($config)) {
            $config = KantFactory::getConfig()->get('session.' . $config);
        }
        return $config;
    }

    public function load($options) {
        $type = ucfirst($options['type']);
        $class = "\\Kant\\Session\\Driver\\{$type}\\Session";
        $object = new $class($options);
        return $object;
    }

}

?>
