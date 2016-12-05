<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache;

use Kant\KantFactory;
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

    public static function platform($config = "") {
        $options = self::parseConfig($config);
        if (self::$_cache == '') {
            self::$_cache = (new self())->connect($options);
        }
        return self::$_cache;
    }

    public static function parseConfig($config = "") {
        if ($config == "") {
            $config = KantFactory::getConfig()->get('cache.default');
        } elseif (is_string($config)) {
            $config = KantFactory::getConfig()->get('cache.' . $config);
        }
        return $config;
    }

    /**
     *
     * Load cache driver
     *
     * @param cache_name string
     * @return object on success
     */
    public function connect($options) {
        switch ($options['type']) {
            case 'memcache':
                $object = new Memcache([
                    'host' => $options['hostname'],
                    'port' => $options['port'],
                    'timeout' => $options['timeout'] > 0 ? $options['timeout'] : 1,
                ]);
                break;
            case 'redis':
                $object = new Redis([
                    'host' => $options['hostname'],
                    'port' => $options['port']
                ]);
                break;
            case 'file':
            case 'default':
                $object = new File();
                break;
        }
        return $object;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters) {
        return call_user_func_array([self::$_cache, $method], $parameters);
    }

}

?>