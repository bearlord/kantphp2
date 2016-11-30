<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Foundation;

use Kant\Foundation\Object;
use Kant\KantFactory;
use Kant\Registry\KantRegistry;
use Kant\Cookie\Cookie;

class Base extends Object {

    //cache
    protected $cache;
    //cookie
    protected $cookie;
    protected $session;

    public function __construct() {
        $this->cache = KantFactory::getCache();
        $this->cookie = $this->_initCookie();
    }

    

    /**
     * Load Cookie
     */
    private function _initCookie() {
        static $cookie = null;
        if (empty($cookie)) {
            $cookieConfig = KantFactory::getConfig()->get('cookie');
            try {
                $cookie = Cookie::getInstance($cookieConfig);
            } catch (RuntimeException $e) {
                throw new Exception('Load Cookie Error: ' . $e->getMessage());
            }
            $this->cookie = $cookie;
        }
        return $cookie;
    }

}
