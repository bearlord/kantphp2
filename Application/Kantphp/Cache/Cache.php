<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache;

!defined('IN_KANT') && exit('Access Denied');

/**
 * Cache factory
 * 
 * @final
 * @version 1.1
 * @since version1.1
 */
//require_once KANT_PATH . 'Cache/CacheFile.php';
//require_once KANT_PATH . 'Cache/CacheMemcache.php';
//require_once KANT_PATH . 'Cache/CacheRedis.php';
//require_once KANT_PATH . 'Cache/CacheBaeMemcache.php';

final class Cache {

    /**
     *
     * Static instance of factory mode
     *
     */
    private static $_cache;

    /**
     *
     * Cache config
     *
     */
    protected $cacheConfig = array(
        //default file cache type
        'defalut' => array(
            'type' => 'file',
        ),
        //memcache type
        'memcache' => array(
            'type' => 'memcache',
            'hostname' => 'localhost',
            'port' => 11211,
            'timeout' => 0,
        ),
        //redis cache type
        'redis' => array(
            'type' => 'redis',
            'hostname' => '127.0.0.1',
            'port' => 6379
        )
    );

    /**
     *
     * Cache List
     *
     */
    protected $cacheList = array();

    /**
     *
     * Construct
     *
     */
    public function __construct() {
        
    }

    /**
     *
     * Get instantce of the final object
     * @static
     *
     * @param cache_config string
     * @return object on success
     */
    public static function getInstance($cacheConfig = '') {
        if ($cacheConfig == '') {
            $cacheConfig = require_once CFG_PATH . 'Cache.php';
        }
        if (self::$_cache == '') {
            self::$_cache = new self();
        }
        if ($cacheConfig != '' && $cacheConfig != self::$_cache->cacheConfig) {
            self::$_cache->cacheConfig = array_merge($cacheConfig, self::$_cache->cacheConfig);
        }
        return self::$_cache;
    }

    /**
     *
     * Get instance of the cache cacheConfig
     *
     * @param cache_name string
     * @return array on success
     */
    public function getCache($cacheName) {
        if (!isset($this->cacheList[$cacheName]) || !is_object($this->cacheList[$cacheName])) {
            $this->cacheList[$cacheName] = $this->load($cacheName);
        }
        return $this->cacheList[$cacheName];
    }

    /**
     *
     * Load cache driver
     *
     * @param cache_name string
     * @return object on success
     */
    public function load($cacheName) {
        $object = null;
        if (isset($this->cacheConfig[$cacheName]['type'])) {
            switch ($this->cacheConfig[$cacheName]['type']) {
                case 'file' :
                    $object = new CacheFile();
                    break;
                case 'memcache' :
                    $memcacheConfig = array(
                        'host' => $this->cacheConfig[$cacheName]['hostname'],
                        'port' => $this->cacheConfig[$cacheName]['port'],
                        'timeout' => $this->cacheConfig[$cacheName]['timeout'] > 0 ? $this->cacheConfig[$cacheName]['timeout'] : 1,
                    );
                    $object = new CacheMemcache($memcacheConfig);
                    break;
                case 'redis':
                    $redisConfig = array(
                        'host' => $this->cacheConfig[$cacheName]['hostname'],
                        'port' => $this->cacheConfig[$cacheName]['port']
                    );
                    $object = new CacheRedis($redisConfig);
                    break;
                case 'baememcache':
                    $baememcacheConfig = array(
                        'appid' => $this->cacheConfig[$cacheName]['appid']
                    );
                    $object = new CacheBaeMemcache($baememcacheConfig);
                    break;
                default :
                    $object = new CacheFile();
            }
        } else {
            $object = new CacheFile();
        }
        return $object;
    }

}

/**
 * 2013-05-23 modified
 */
?>