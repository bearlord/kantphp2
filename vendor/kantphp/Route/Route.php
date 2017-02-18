<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Route;

use Kant\Factory;

class Route {

    /**
     * Rules
     * @var array 
     */
    private static $rules = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'HEAD' => [],
        'OPTIONS' => [],
        '*' => [],
    ];

    /**
     * Restful
     * @var type 
     */
    private static $rest = [
        'index' => ['GET', '', 'index'],
        'create' => ['GET', '/create', 'create'],
        'edit' => ['GET', '/:id/edit', 'edit'],
        'read' => ['GET', '/:id', 'read'],
        'save' => ['POST', '', 'save'],
        'update' => ['PUT', '/:id', 'update'],
        'delete' => ['DELETE', '/:id', 'delete'],
    ];

    /**
     * Route map
     * 
     * @var array 
     */
    private static $map = [];

    /**
     * Route pattern
     * 
     * @var array 
     */
    private static $pattern = [];

    /**
     * Rule map addition
     * 
     * @param type $map
     * @param type $route
     * @return type
     */
    public static function map($map = '', $route = '') {
        return self::setting('map', $map, $route);
    }

    /**
     * Pattern filter addtion
     * 
     * Variable rule addtion
     * @param type $name
     * @param type $rule
     * @return type
     */
    public static function pattern($name = '', $rule = '') {
        return self::setting('pattern', $name, $rule);
    }

    /**
     * Attribute setting
     * 
     * @param type $var
     * @param type $name
     * @param type $value
     * @return type
     */
    private static function setting($var, $name = '', $value = '') {
        if (is_array($name)) {
            self::${$var} = self::${$var} + $name;
        } elseif (empty($value)) {
            return empty($name) ? self::${$var} : self::${$var}[$name];
        } else {
            self::${$var}[$name] = $value;
        }
    }

    /**
     * Improt rule
     * 
     * @access public
     * @param array $rule
     * @param string $type
     * @return void
     */
    public static function import(array $rule, $type = '*') {
        if (isset($rule['__pattern__'])) {
            self::pattern($rule['__pattern__']);
            unset($rule['__pattern__']);
        }

        if (isset($rule['__map__'])) {
            self::map($rule['__map__']);
            unset($rule['__map__']);
        }

        if (isset($rule['__rest__'])) {
            self::resource($rule['__rest__']);
            unset($rule['__rest__']);
        }

        $type = strtoupper($type);
        foreach ($rule as $key => $val) {
            if (is_numeric($key)) {
                $key = array_shift($val);
            }
            if (empty($val)) {
                continue;
            }
            if (0 === strpos($key, '[')) {
                $key = substr($key, 1, -1);
                $result = ['routes' => $val, 'option' => [], 'pattern' => []];
            } elseif (is_array($val)) {
                $result = ['route' => $val[0], 'option' => $val[1], 'pattern' => isset($val[2]) ? $val[2] : []];
            } else {
                $result = ['route' => $val, 'option' => [], 'pattern' => []];
            }
            self::$rules[$type][$key] = $result;
        }
    }

    /**
     * Register get request rule
     * 
     * @param string/array $rule
     * @param type $route
     * @param array $option
     * @param array $pattern
     */
    public static function get($rule, $route = '', $option = [], $pattern = []) {
        self::rule($rule, $route, 'GET', $option, $pattern);
    }

    /**
     * Register post request rule
     * 
     * @param type $rule
     * @param type $route
     * @param type $option
     * @param type $pattern
     */
    public static function post($rule, $route = '', $option = [], $pattern = []) {
        self::rule($rule, $route, 'POST', $option, $pattern);
    }

    /**
     * Rescource rule
     * 
     * @param string $rule
     * @param type $route
     * @param type $option
     * @param type $pattern
     */
    public static function resource($rule, $route = '', $option = [], $pattern = []) {
        if (is_array($rule)) {
            foreach ($rule as $key => $val) {
                if (is_array($val)) {
                    list($val, $option, $pattern) = array_pad($val, 3, []);
                }
                self::resource($key, $val, $option, $pattern);
            }
        } else {
            if (strpos($rule, '.')) {
                // 注册嵌套资源路由
                $array = explode('.', $rule);
                $last = array_pop($array);
                $item = [];
                foreach ($array as $val) {
                    $item[] = $val . '/:' . (isset($option['var'][$val]) ? $option['var'][$val] : $val . '_id');
                }
                $rule = implode('/', $item) . '/' . $last;
            }
            // 注册资源路由
            foreach (self::$rest as $key => $val) {
                if ((isset($option['only']) && !in_array($key, $option['only'])) || (isset($option['except']) && in_array($key, $option['except']))) {
                    continue;
                }
                if (strpos($val[1], ':id') && isset($option['var'][$rule])) {
                    $val[1] = str_replace(':id', ':' . $option['var'][$rule], $val[1]);
                }
                $item = ltrim($rule . $val[1], '/');
                self::rule($item ? $item . '$' : '', $route . '/' . $val[2], $val[0], $option, $pattern);
            }
        }
    }

    /**
     * Set rule group
     * 
     * @access public
     * @param array $option
     * @return void
     */
    public static function setGroup($name) {
        self::$group = $name;
    }

    /**
     * Set rule option
     * 
     * @access public
     * @param array $option 路由参数
     * @return void
     */
    public static function setOption($option) {
        self::$option = $option;
    }

    /**
     * Register route group
     * 
     * @access public
     * @param string|array $name
     * @param array|\Closure $routes
     * @param array $option
     * @param string $type
     * @param array $pattern
     * @return void
     */
    public static function group($name, $routes, $option = [], $type = '*', $pattern = []) {
        if (is_array($name)) {
            $option = $name;
            $name = isset($option['name']) ? $option['name'] : '';
        }
        $type = strtoupper($type);
        if (!empty($name)) {
            if ($routes instanceof \Closure) {
                self::setGroup($name);
                call_user_func_array($routes, []);
                self::setGroup(null);
                self::$rules[$type][$name]['option'] = $option;
                self::$rules[$type][$name]['pattern'] = $pattern;
            } else {
                self::$rules[$type][$name] = ['routes' => $routes, 'option' => $option, 'pattern' => $pattern];
            }
        } else {
            if ($routes instanceof \Closure) {
                self::setOption($option);
                call_user_func_array($routes, []);
                self::setOption([]);
            } else {
                self::rule($routes, '', $type, $option, $pattern);
            }
        }
    }

    /**
     * register request rules
     * 
     * @param string/array $rule
     * @param type $route
     * @param type $type
     * @param type $option
     * @param type $pattern
     */
    public static function rule($rule, $route = '', $type = '*', $option = [], $pattern = []) {
        if (strpos($type, '|')) {
            foreach (explode('|', $type) as $val) {
                self::rule($rule, $route, $val, $option);
            }
        } else {
            if (is_array($rule)) {
                if (isset($rule['__pattern__'])) {
                    self::pattern($rule['__pattern__']);
                    unset($rule['__pattern__']);
                }
                if (isset($rule['__map__'])) {
                    self::map($rule['__map__']);
                    unset($rule['__map__']);
                }
                if (isset($rule['__rest__'])) {
                    self::resource($rule['__rest__']);
                    unset($rule['__rest__']);
                }
                foreach ($rule as $key => $val) {
                    if (is_numeric($key)) {
                        $key = array_shift($val);
                    }
                    if (0 === strpos($key, '[')) {
                        if (empty($val)) {
                            break;
                        }
                        $key = substr($key, 1, -1);
                        $result = ['routes' => $val, 'option' => $option, 'pattern' => $pattern];
                    } elseif (is_array($val)) {
                        $result = ['route' => $val[0], 'option' => $val[1], 'pattern' => isset($val[2]) ? $val[2] : []];
                    } else {
                        $result = ['route' => $val, 'option' => $option, 'pattern' => $pattern];
                    }
                    self::$rules[$type][$key] = $result;
                }
            } else {
                if (0 === strpos($rule, '[')) {
                    $rule = substr($rule, 1, -1);
                    $result = ['routes' => $route, 'option' => $option, 'pattern' => $pattern];
                } else {
                    $result = ['route' => $route, 'option' => $option, 'pattern' => $pattern];
                }
                self::$rules[$type][$rule] = $result;
            }
        }
    }

    /**
     * URL check
     * 
     * @param string $url
     * @param type $depr
     * @param type $checkDomain
     * @return boolean
     */
    public static function check($url, $depr = '/') {
        if ('/' != $depr) {
            $url = str_replace($depr, '/', $url);
        }

        if (empty($url)) {
            $url = '/';
        }

        if (isset(self::$map[$url])) {
            return self::parseUrl(self::$map[$url], $depr);
        }

        $rules = self::$rules[REQUEST_METHOD];

        if (!empty(self::$rules['*'])) {
            $rules = array_merge(self::$rules['*'], $rules);
        }
        if (!empty($rules)) {
            foreach ($rules as $rule => $val) {
                $option = $val['option'];
                $pattern = $val['pattern'];
                if (!empty($val['routes'])) {
                    if (0 !== strpos($url, $rule)) {
                        continue;
                    }
                    foreach ($val['routes'] as $key => $route) {
                        if (is_numeric($key)) {
                            $key = array_shift($route);
                        }
                        $url1 = substr($url, strlen($rule) + 1);
                        if (is_array($route)) {
                            $option1 = $route[1];
                            $pattern = array_merge($pattern, isset($route[2]) ? $route[2] : []);
                            $route = $route[0];
                            $option = array_merge($option, $option1);
                        }
                        $result = self::checkRule($key, $route, $url1, $pattern, $option);
                        if (false !== $result) {
                            return $result;
                        }
                    }
                } else {
                    if (is_numeric($rule)) {
                        $rule = array_shift($val);
                    }
                    $route = !empty($val['route']) ? $val['route'] : '';
                    $result = self::checkRule($rule, $route, $url, $pattern, $option);
                    if ($result !== false) {
                        return $result;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Parse Url
     * 
     * @param type $url
     * @param type $depr
     * @param type $autoSearch
     * @param type $paramType
     * @return type
     */
    public static function parseUrl($url, $depr = '/', $autoSearch = false, $paramType = 0) {
        if ('/' != $depr) {
            $url = str_replace($depr, '/', $url);
        }

        $result = self::parseRoute($url, $autoSearch, true, $paramType);

        if (!empty($result['var'])) {
            $_GET = array_merge($result['var'], $_GET);
        }
        return ['type' => 'module', 'module' => $result['route']];
    }

    /**
     * Check Rule
     * 
     * @param type $rule
     * @param \Closure $route
     * @param type $url
     * @param type $pattern
     * @param type $option
     * @return boolean
     */
    private static function checkRule($rule, $route, $url, $pattern, $option) {
        if (isset($pattern['__url__']) && !preg_match('/^' . $pattern['__url__'] . '/', $url)) {
            return false;
        }
        $len1 = substr_count($url, '/');
        $len2 = substr_count($rule, '/');
        if ($len1 >= $len2 || strpos($rule, '[')) {
            if ('$' == substr($rule, -1, 1)) {
                if ($len1 != $len2 && false === strpos($rule, '[')) {
                    return false;
                } else {
                    $rule = substr($rule, 0, -1);
                }
            }
            $pattern = array_merge(self::$pattern, $pattern);
            $match = self::match($url, $rule, $pattern);
            if (false !== $match = self::match($url, $rule, $pattern)) {
                if ($route instanceof \Closure) {
                    return ['type' => 'function', 'function' => $route, 'params' => $match];
                }
                return self::parseRule($rule, $route, $url, $match);
            }
        }
        return false;
    }

    /**
     * Match url
     * 
     * @param type $url
     * @param type $rule
     * @param type $pattern
     * @return boolean
     */
    protected static function match($url, $rule, $pattern) {
        $m1 = explode('/', $url);
        $m2 = explode('/', $rule);
        $var = [];
        foreach ($m2 as $key => $val) {
            if (false !== strpos($val, '<') && preg_match_all('/<(\w+(\??))>/', $val, $matches)) {
                $value = [];
                foreach ($matches[1] as $name) {
                    if (strpos($name, '?')) {
                        $name = substr($name, 0, -1);
                        $replace[] = '((' . (isset($pattern[$name]) ? $pattern[$name] : '') . ')?)';
                    } else {
                        $replace[] = '(' . (isset($pattern[$name]) ? $pattern[$name] : '') . ')';
                    }
                    $value[] = $name;
                }
                $val = str_replace($matches[0], $replace, $val);
                if (preg_match('/^' . $val . '$/', $m1[$key], $match)) {
                    array_shift($match);
                    $match = array_slice($match, 0, count($value));
                    $var = array_merge($var, array_combine($value, $match));
                    continue;
                } else {
                    return false;
                }
            }
            if (0 === strpos($val, '[:')) {
                $val = substr($val, 1, -1);
            }
            if (0 === strpos($val, ':')) {
                $name = substr($val, 1);
                if (isset($m1[$key]) && isset($pattern[$name]) && !preg_match('|^' . $pattern[$name] . '$|', $m1[$key])) {
                    return false;
                }
                $var[$name] = isset($m1[$key]) ? $m1[$key] : '';
            } elseif (0 !== strcasecmp($val, $m1[$key])) {
                return false;
            }
        }
        return $var;
    }

    /**
     * Parse Rule
     * 
     * @param type $rule
     * @param type $route
     * @param type $pathinfo
     * @param type $matches
     * @return string
     */
    private static function parseRule($rule, $route, $pathinfo, $matches) {
        $paths = explode('/', $pathinfo);
        $url = is_array($route) ? $route[0] : $route;
        $rule = explode('/', $rule);
        foreach ($rule as $item) {
            $fun = '';
            if (0 === strpos($item, '[:')) {
                $item = substr($item, 1, -1);
            }
            if (0 === strpos($item, ':')) {
                $var = substr($item, 1);
                $matches[$var] = array_shift($paths);
            } else {
                array_shift($paths);
            }
        }
        // 替换路由地址中的变量
        foreach ($matches as $key => $val) {
            if (false !== strpos($url, ':' . $key)) {
                $url = str_replace(':' . $key, $val, $url);
                unset($matches[$key]);
            }
        }
        if (0 === strpos($url, '/') || 0 === strpos($url, 'http')) {
            ob_start();
            $result = ['type' => 'redirect', 'url' => $url, 'status' => (is_array($route) && isset($route[1])) ? $route[1] : 301];
            ob_end_flush();
        } elseif (0 === strpos($url, '\\')) {
            $result = ['type' => 'method', 'method' => is_array($route) ? [$url, $route[1]] : $url, 'params' => $matches];
        } elseif (0 === strpos($url, '@')) {
            $result = ['type' => 'controller', 'controller' => substr($url, 1), 'params' => $matches];
        } else {
            $result = self::parseRoute($url);
            $var = array_merge($matches, $result['var']);
            self::parseUrlParams(implode('/', $paths), $var);
            $result = ['type' => 'module', 'module' => $result['route']];
        }
        return $result;
    }

    /**
     * Parse route
     * 
     * @param type $pathinfo
     */
    protected static function parseRoute($pathinfo) {
        $route = [null, null, null];
        $var = [];
        $pathinfo = trim($pathinfo, "/");
        //Special pathinof as demo/index/get/a,100/b,101?c=102&d=103
        if (strpos($pathinfo, "?") !== false) {
            $parse = explode("?", $pathinfo);
            $path = explode('/', $parse[0]);
            if (!empty($parse[1])) {
                parse_str($parse[1], $query);
                foreach ($query as $key => $val) {
                    $dispatcher[$key] = urldecode($val);
                }
            }
        } else {
            //Normal pathinfo as demo/index/get/a,100/b,101
            $path = explode('/', $pathinfo);
        }

        $routeConfig = Factory::getConfig()->get("route");
        $module = array_shift($path);
        $module = !empty($module) ? $module : $routeConfig['module'];
        $controller = !empty($path) ? array_shift($path) : $routeConfig['ctrl'];
        $action = !empty($path) ? array_shift($path) : $routeConfig['act'];
        if ($action) {
            if (strpos($action, "?") !== false) {
                $action = substr($action, 0, strpos($action, "?"));
            }
            $urlsuffix = Factory::getConfig()->get('url_suffix');
            if ($urlsuffix) {
                if (strpos($action, "&") !== false) {
                    $action = substr($action, 0, strpos($action, $urlsuffix));
                }
            } else {
                if (strpos($action, "&") !== false) {
                    $action = substr($action, 0, strpos($action, "&"));
                }
            }
            while ($next = array_shift($path)) {
                $query = preg_split("/[?&]/", $next);
                if (!empty($query)) {
                    foreach ($query as $key => $val) {
                        $arr = preg_split("/[,:=-]/", $val, 2);
                        if (!empty($arr[1])) {
                            $var[$arr[0]] = urldecode($arr[1]);
                        }
                    }
                }
            }
        }

        $route = [$module, $controller, $action];
        return ['route' => $route, 'var' => $var];
    }

    /**
     * Parse url params
     * 
     * @param string $url
     * @param string $var
     */
    protected static function parseUrlParams($url, $var) {
        $_GET = array_merge($var, $_GET);
    }

}
