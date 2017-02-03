<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache\Driver;

/**
 * Redis cache
 */
class Redis {

    private $redis = null;

    public function __construct($redisConfig) {
        $this->redis = new Redis;
        $this->redis->connect($redisConfig['host'], $redisConfig['port']);
    }

    /**
     * 
     * Retrieve item from the server
     * 
     * @param name string
     * @return value string
     */
    public function get($name) {
        $value = $this->redis->get($name);
        $value = unserialize($value);
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
    public function set($name, $value, $expire = 0) {
        $value = serialize($value);
        if ($expire > 0) {
            return $this->redis->setex($name, $expire, $value);
        } else {
            return $this->redis->set($name, $value);
        }
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
        return $this->redis->delete($name);
    }

    /**
     * 
     * Flush all existing items at the server
     * @return boolean
     */
    public function flush() {
        return $this->redis->flushDB();
    }

}

?>