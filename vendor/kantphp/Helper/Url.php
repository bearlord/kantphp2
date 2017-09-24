<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Helper;

use Kant\Kant;
use Kant\Routing\UrlGenerator;

/**
 * Url provides a set of static methods for managing URLs.
 */
class Url
{

    /**
     * Returns a value indicating whether a URL is relative.
     * A relative URL does not have host info part.
     * 
     * @param string $url
     *            the URL to be checked
     * @return boolean whether the URL is relative
     */
    public static function isRelative($url)
    {
        return strncmp($url, '//', 2) && strpos($url, '://') === false;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method            
     * @param array $args            
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = new UrlGenerator(Kant::$app->getRouter()->getRoutes(), Kant::$app->getRequest());
        
        return call_user_func_array([
            $instance,
            $method
        ], $args);
    }
}
