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
     * Creates a URL for the given route.
     *
     * This method will use [[\yii\web\UrlManager]] to create a URL.
     *
     * You may specify the route as a string, e.g., `site/index`. You may also use an array
     * if you want to specify additional query parameters for the URL being created. The
     * array format must be:
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1&param2=value2
     * ['site/index', 'param1' => 'value1', 'param2' => 'value2']
     * ```
     *
     * If you want to create a URL with an anchor, you can use the array format with a `#` parameter.
     * For example,
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1#name
     * ['site/index', 'param1' => 'value1', '#' => 'name']
     * ```
     *
     * A route may be either absolute or relative. An absolute route has a leading slash (e.g. `/site/index`),
     * while a relative route has none (e.g. `site/index` or `index`). A relative route will be converted
     * into an absolute one by the following rules:
     *
     * - If the route is an empty string, the current [[\yii\web\Controller::route|route]] will be used;
     * - If the route contains no slashes at all (e.g. `index`), it is considered to be an action ID
     *   of the current controller and will be prepended with [[\yii\web\Controller::uniqueId]];
     * - If the route has no leading slash (e.g. `site/index`), it is considered to be a route relative
     *   to the current module and will be prepended with the module's [[\yii\base\Module::uniqueId|uniqueId]].
     *
     * Starting from version 2.0.2, a route can also be specified as an alias. In this case, the alias
     * will be converted into the actual route first before conducting the above transformation steps.
     *
     * Below are some examples of using this method:
     *
     * ```php
     * // /index.php?r=site%2Findex
     * echo Url::toRoute('site/index');
     *
     * // /index.php?r=site%2Findex&src=ref1#name
     * echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);
     *
     * // http://www.example.com/index.php?r=site%2Findex
     * echo Url::toRoute('site/index', true);
     *
     * // https://www.example.com/index.php?r=site%2Findex
     * echo Url::toRoute('site/index', 'https');
     *
     * // /index.php?r=post%2Findex     assume the alias "@posts" is defined as "post/index"
     * echo Url::toRoute('@posts');
     * ```
     *
     * @param string|array $route use a string to represent a route (e.g. `index`, `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param bool|string $scheme the URI scheme to use in the generated URL:
     *
     * - `false` (default): generating a relative URL.
     * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::$hostInfo]].
     * - string: generating an absolute URL with the specified scheme (either `http`, `https` or empty string
     *   for protocol-relative URL).
     *
     * @return string the generated URL
     * @throws InvalidParamException a relative route is given while there is no active controller
     */
    public static function toRoute($route, $scheme = false)
    {
        $route = static::normalizeRoute($route);

        return $route;
    }

    /**
     * Normalizes route and makes it suitable for UrlManager. Absolute routes are staying as is
     * while relative routes are converted to absolute ones.
     *
     * A relative route is a route without a leading slash, such as "view", "post/view".
     *
     * - If the route is an empty string, the current [[\yii\web\Controller::route|route]] will be used;
     * - If the route contains no slashes at all, it is considered to be an action ID
     *   of the current controller and will be prepended with [[\yii\web\Controller::uniqueId]];
     * - If the route has no leading slash, it is considered to be a route relative
     *   to the current module and will be prepended with the module's uniqueId.
     *
     * Starting from version 2.0.2, a route can also be specified as an alias. In this case, the alias
     * will be converted into the actual route first before conducting the above transformation steps.
     *
     * @param string $route the route. This can be either an absolute route or a relative route.
     * @return string normalized route suitable for UrlManager
     * @throws InvalidParamException a relative route is given while there is no active controller
     */
    public static function normalizeRoute($route)
    {
        $route = Kant::getAlias((string) $route);
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return ltrim($route, '/');
        }

        // relative route
        if (Kant::$app->controller === null) {
            throw new InvalidParamException("Unable to resolve the relative route: $route. No active controller is available.");
        }

        if (strpos($route, '/') === false) {
            // empty or an action ID
            return $route === '' ? Kant::$app->controller->getRoute() : Kant::$app->controller->getUniqueId() . '/' . $route;
        } else {
            // relative to module
            return ltrim(Kant::$app->controller->module->getUniqueId() . '/' . $route, '/');
        }
    }

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
