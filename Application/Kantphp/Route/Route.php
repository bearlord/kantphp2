<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Route;

use Kant\KantFactory;

!defined('IN_KANT') && exit('Access Denied');

class Route {

    private static $_instance = null;
    private $_rules = array();
    protected $_enableDynamicMatch = true;
    protected $_dynamicRule = array();
    protected $get;
    protected $post;
    protected $request;

    /**
     * Module type
     * 
     * @var type 
     */
    protected $_moduleType = false;
    protected $_urlSuffix;
    private static $pattern = [];

    /**
     * Rules
     * @var type 
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

    public function __construct() {
        
    }

    /**
     * register get request rule
     * 
     * @param string/array $rule
     * @param type $route
     * @param array $option
     * @param array $pattern
     */
    public static function get($rule, $route = '', $option = [], $pattern = []) {
        self::register($rule, $route, 'GET', $option, $pattern);
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
    public static function register($rule, $route = '', $type = '*', $option = [], $pattern = []) {
        if (strpos($type, '|')) {
            foreach (explode('|', $type) as $val) {
                self::register($rule, $route, $val, $option);
            }
        } else {
            if (is_array($rule)) {
                // 检查域名部署
                if (isset($rule['__domain__'])) {
                    self::domain($rule['__domain__']);
                    unset($rule['__domain__']);
                }
                // 检查变量规则
                if (isset($rule['__pattern__'])) {
                    self::pattern($rule['__pattern__']);
                    unset($rule['__pattern__']);
                }
                // 检查路由映射
                if (isset($rule['__map__'])) {
                    self::map($rule['__map__']);
                    unset($rule['__map__']);
                }
                // 检查资源路由
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
                    if (false !== $result) {
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
            $match = self::matchUrl($url, $rule, $pattern);
            if (false !== $match = self::matchUrl($url, $rule, $pattern)) {
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
    protected static function matchUrl($url, $rule, $pattern) {
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
                if (isset($m1[$key]) && isset($pattern[$name]) && !preg_match('/^' . $pattern[$name] . '$/', $m1[$key])) {
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
            // 路由到方法
            $result = ['type' => 'method', 'method' => is_array($route) ? [$url, $route[1]] : $url, 'params' => $matches];
        } elseif (0 === strpos($url, '@')) {
            // 路由到控制器
            $result = ['type' => 'controller', 'controller' => substr($url, 1), 'params' => $matches];
        } else {
            // 解析路由地址
            $result = self::parseRoute($url);
            $var = array_merge($matches, $result['var']);
            self::parseUrlParams(implode('/', $paths), $var);
            // 路由到模块/控制器/操作
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
        //Special pathinof as demo/index/get/a,100/b,101?c=102&d=103
        if (strpos($pathinfo, "?") !== false) {
            $parse = explode("?", $pathinfo);
            $path = explode('/', $parse[0]);
            if (!empty($parse[1])) {
                parse_str($parse[1], $query);
                foreach ($query as $key => $val) {
                    $dispatchInfo[$key] = urldecode($val);
                }
            }
        } else {
            //Normal pathinfo as demo/index/get/a,100/b,101
            $path = explode('/', $pathinfo);
        }
        if ($path) {
            $module = array_shift($path);
            $controller = !empty($path) ? array_shift($path) : null;
            $action = !empty($path) ? array_shift($path) : null;
            if ($action) {
                if (strpos($action, "?") !== false) {
                    $action = substr($action, 0, strpos($action, "?"));
                }
                $urlsuffix = KantFactory::getConfig()->reference('url_suffix');
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
        }
        $route = [$module, $controller, $action];
        return ['route' => $route, 'var' => $var];
    }

    protected static function parseUrlParams($url, $var) {
        $_GET = array_merge($var, $_GET);
    }

    /**
     * Singleton instance
     * 
     * @return array
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Set url suffix
     * 
     * @param type $var
     * @return type
     */
    public function setUrlSuffix($var) {
        return $this->_urlSuffix = $var;
    }

    /*
     * Get url suffix
     * 
     */

    public function getUrlSuffix() {
        return $this->_urlSuffix;
    }

    /**
     * Get rules
     *
     * @param string $regex
     * @return array
     */
    public function rules($regex = null) {
        if (null === $regex) {
            return $this->_rules;
        }
        return isset($this->_rules[$regex]) ? $this->_rules[$regex] : null;
    }

    /**
     * Add rule
     *
     * @param array $rule
     * @param boolean $overwrite
     */
    public function add($rules, $overwrite = true) {
        $rules = (array) $rules;
        if ($overwrite) {
            $this->_rules = $rules + $this->_rules;
        } else {
            $this->_rules += $rules;
        }

        return $this;
    }

    /**
     * Remove rule
     *
     * @param string $regex
     */
    public function remove($regex) {
        unset($this->_rules[$regex]);
        return $this;
    }

    /**
     * Enable or disable dynamic match
     *
     * @param boolean $flag
     * @param array $opts
     * @return Cola_Router
     */
    public function enableDynamicMatch($flag = true, $opts = array()) {
        $this->_enableDynamicMatch = true;
        $this->_dynamicRule = $opts + $this->_dynamicRule;
        return $this;
    }

    /**
     * Match path
     *
     * @param string $path
     * @return boolean
     */
    public function match($pathInfo = null) {
        $pathInfo = trim($pathInfo, '/');
        if (!empty($this->_rules)) {
            $pathInfo = str_replace("$1", 4, $pathInfo);
            foreach ($this->_rules as $regex => $rule) {
                $res = preg_match($regex, $pathInfo, $matches);
                if ($matches) {
                    $pathInfo = $this->_rules[$regex];
                    for ($i = 1; $i < count($matches); $i++) {
                        $pathInfo = str_replace("$" . $i, $matches[$i], $pathInfo);
                    }
                    break;
                }
            }
        }
        return $this->_dynamicMatch($pathInfo);
    }

    /**
     * Dynamic Match
     *
     * @param string $pathInfo
     * @return array $dispatchInfo
     */
    protected function _dynamicMatch($pathInfo) {
        //Special pathinof as demo/index/get/a,100/b,101?c=102&d=103
        if (strpos($pathInfo, "?") > 0) {
            $parse = explode("?", $pathInfo);
            $tmp = explode('/', $parse[0]);
            if (!empty($parse[1])) {
                parse_str($parse[1], $query);
                foreach ($query as $key => $val) {
                    $dispatchInfo[$key] = urldecode($val);
                }
            }
        } else {
            //Normal pathinfo as demo/index/get/a,100/b,101
            $tmp = explode('/', $pathInfo);
        }
        if ($module = current($tmp)) {
            $dispatchInfo['module'] = ucfirst(current($tmp));
        } else {
            $dispatchInfo['module'] = ucfirst($this->_dynamicRule['module']);
        }
        if ($controller = next($tmp)) {
            $dispatchInfo['ctrl'] = ucfirst($controller);
        } else {
            $dispatchInfo['ctrl'] = ucfirst($this->_dynamicRule['ctrl']);
        }
        if ($action = next($tmp)) {
            if (strpos($action, "?") !== false) {
                $action = substr($action, 0, strpos($action, "?"));
            }
            if (strpos($action, "&") !== false) {
                $action = substr($action, 0, strpos($action, "."));
            }
            $dispatchInfo['act'] = ucfirst($action);
        } else {
            $dispatchInfo['act'] = $this->_dynamicRule['act'];
        }
        while (false !== ($next = next($tmp))) {
            $query = preg_split("/[?&]/", $next);
            if (!empty($query)) {
                foreach ($query as $key => $val) {
                    $arr = preg_split("/[,:=-]/", $val, 2);
                    if (!empty($arr[1])) {
                        $dispatchInfo[$arr[0]] = urldecode($arr[1]);
                    }
                }
            }
        }
        return $dispatchInfo;
    }

}
