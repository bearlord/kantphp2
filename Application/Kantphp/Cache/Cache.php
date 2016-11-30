<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache;

use Kant\Cache\Driver\File;
use Kant\Cache\Driver\Memcache;
use Kant\Cache\Driver\Redis;

/**
 * Cache factory
 * 
 * @final
 * @version 1.1
 * @since version1.1
 */
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
        'defalut' => [
            'type' => 'file',
        ],
        //memcache type
        'memcache' => [
            'type' => 'memcache',
            'hostname' => 'localhost',
            'port' => 11211,
            'timeout' => 0,
        ],
        //redis cache type
        'redis' => [
            'type' => 'redis',
            'hostname' => '127.0.0.1',
            'port' => 6379
        ]
    );

    /**
     *
     * Construct
     *
     */
    public function __construct($config = '') {
        if ($config == '') {
            $config = $this->cacheConfig['default'];
        }
        $this->connect($config);
    }

    /**
     *
     * Get instantce of the final object
     * @static
     *
     * @param cache_config string
     * @return object on success
     */
    public static function getInstance($config = '') {
        if (self::$_cache == '') {
            self::$_cache = new self($config);
        }
        return self::$_cache;
    }

    /**
     *
     * Load cache driver
     *
     * @param cache_name string
     * @return object on success
     */
    public function connect($config) {
        switch ($config['type']) {
            case 'memcache':
                $memcacheConfig = array(
                    'host' => $config['hostname'],
                    'port' => $config['port'],
                    'timeout' => $config['timeout'] > 0 ? $config['timeout'] : 1,
                );
                $object = new Memcache($memcacheConfig);
                break;
            case 'redis':
                $redisConfig = array(
                        'host' => $config['hostname'],
                        'port' => $config['port']
                    );
                    $object = new Redis($redisConfig);
                    break;
            case 'file':
            case 'default':
                $object = new File();
                break;
        }
        return $object;
    }

}

?>