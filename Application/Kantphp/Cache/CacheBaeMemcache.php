<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache;

!defined('IN_KANT') && exit('Access Denied');

//require_once 'BaeMemcache.class.php';
/**
 * BAE Memcache cache
 */
class CacheBaeMemcache {

    private $memcache = null;

    public function __construct($baememcacheConfig) {
        $this->memcache = new BaeMemcache();
        $this->memcache->set_shareAppid($baememcacheConfig['appid']);
    }

    /**
     * 
     * Retrieve item from the server
     * 
     * @param name string
     * @return value string
     */
    public function get($name) {
        $value = $this->memcache->get($name);
        return $value;
    }

    /**
     * 
     * Store data at the server 
     * 
     * @param name string
     * @param value string 
     * @param flag string 
     * @param expire integer
     * 
     * @return boolen
     */
    public function set($name, $value, $expire = 0, $flag = '') {
        return $this->memcache->set($name, $value, '', $expire);
    }

    /**
     * 
     * Delete item from the server
     * 
     * @param name string
     * 
     * @return boolean
     */
    public function delete($name) {
        return $this->memcache->delete($name);
    }

    /**
     * 
     * Flush all existing items at the server
     * @return boolean
     */
    public function flush() {
        return;
//		return $this->memcache->flush();
    }

}

?>