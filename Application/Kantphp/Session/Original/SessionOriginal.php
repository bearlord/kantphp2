<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Session;

class SessionOriginal {

    //Session setting: gc_maxlifetime
    private static $_setting;

    public function __construct($setting) {
        self::$_setting = $setting;
        self::_setSessionModule();
    }

    /**
     * Set Session Module
     */
    private function _setSessionModule() {
        if (function_exists('session_status')) {
            if (session_status() == PHP_SESSION_ACTIVE) {
                return true;
            }
        } else {
            if (isset($_SESSION)) {
                return true;
            }
        }
        session_start();
        setcookie(session_name(), session_id(), time() + self::$_setting['maxlifetime'], "/");
    }

}

?>
