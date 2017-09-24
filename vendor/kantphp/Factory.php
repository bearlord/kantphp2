<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant;

use Kant\Config\Config;
use Kant\Pathinfo\Pathinfo;

class Factory
{

    /**
     * Object container
     */
    public static $container = [
        'application' => '',
        'config' => '',
        'route' => '',
        'session' => '',
        'db' => '',
        'pathinfo' => ''
    ];

    /**
     * Get config object
     */
    public static function getConfig()
    {
        if (! self::$container['config']) {
            self::$container['config'] = new Config();
        }
        return self::$container['config'];
    }

    /**
     * Get Pathinfo object
     */
    public static function getPathInfo()
    {
        if (! self::$container['pathinfo']) {
            self::$container['pathinfo'] = Pathinfo::getInstance();
        }
        return self::$container['pathinfo'];
    }
}
