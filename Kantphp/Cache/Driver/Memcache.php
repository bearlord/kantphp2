<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache\Driver;

/**
 * Memcache cache
 */
class Memcache {

    private $memcache = null;

    public function __construct($memcacheConfig) {
        $this->memcache = new \Memcache;
        $this->memcache->connect($memcacheConfig['host'], $memcacheConfig['port'], $memcacheConfig['timeout']);
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
    public function set($name, $value, $expire = 0, $flag = MEMCACHE_COMPRESSED) {
        $flag = ($flag == MEMCACHE_COMPRESSED) ? $flag : 0;
        return $this->memcache->set($name, $value, $flag, $expire);
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
        return $this->memcache->flush();
    }

}

?>