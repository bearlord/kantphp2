<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

use Kant\Registry\KantRegistry;

!defined('IN_KANT') && exit('Access Denied');

class Base {

    protected $get;
    protected $post;
    protected $route;
    protected $request;
    protected $environment = 'Development';
    protected $input;
    //cache
    protected $cache;
    //cookie
    protected $cookie;
    protected $session;

    public function __construct() {
        $this->cache = $this->_initCache();
        $this->cookie = $this->_initCookie();
        $this->input = Help\Input::getInstance();
    }

    /**
     *
     * Load third-party libary
     * 
     * @param string $classname
     * @param integer $initialize
     * @return
     */
    public function library($classname, $initialize = 0) {
        $filepath = APP_PATH . 'Libary' . DIRECTORY_SEPARATOR . $classname . '.php';
        $key = md5($classname);
        if (file_exists($filepath)) {
            include_once $filepath;
            return true;
        }
    }

    /**
     *
     * Load model
     *
     * @param classname string
     * @param initialize integer[0,1]
     */
    public function model($classname, $initialize = 1, $module = '') {
        static $classes = array();
        if ($module == '') {
            $dispatchInfo = KantRegistry::get('dispatchInfo');
            $module = isset($dispatchInfo['module']) ? $dispatchInfo['module'] : '';
        }
        $classname = ucfirst($classname) . 'Model';
        if ($module) {
            $filepath = APP_PATH . 'Module' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $classname . '.php';
        } else {
            $filepath = APP_PATH . 'Model' . DIRECTORY_SEPARATOR . $classname . '.php';
        }
        $key = md5($filepath . $classname);
        if (file_exists($filepath)) {
            include_once $filepath;
            if ($initialize) {
                if (!empty($classes[$key])) {
                    return $classes[$key];
                }
                $namespace = "$module\\Model\\";
                $classname = $namespace . $classname;
                $classes[$key] = new $classname;
            } else {
                $classes[$key] = true;
            }
            return $classes[$key];
        }
    }

    /**
     * 
     * Page redirection with message 
     * 
     * @param string $message
     * @param string $url
     * @param integer $second
     */
    public function redirect($message, $url = 'goback', $second = 3) {
        $redirectTpl = KantRegistry::get('config')->reference('redirect_tpl');
        if ($redirectTpl) {
            include TPL_PATH . $redirectTpl . '.php';
        } else {
            include KANT_PATH . 'View' . DIRECTORY_SEPARATOR . 'system/redirect.php';
        }
        exit();
    }

    /**
     * Get current user defined language
     * 
     * @return
     */
    public function getLang() {
        static $lang = null;
        if (empty($lang)) {
            $config = KantRegistry::get('config')->reference('cookie');
            $lang = !empty($_COOKIE['lang']) ? $_COOKIE['lang'] : $config['lang'];
            if (empty($lang)) {
                $lang = 'en_US';
            }
        }
        return $lang;
    }

    /**
     * Language localization
     * 
     * @staticvar array $LANG
     * @param string $language
     * @return array
     */
    public function lang($language = 'no_language') {
        static $LANG = array();
        if (!$LANG) {
            $lang = $this->getLang();
            require KANT_PATH . 'Locale' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'System.php';
            if (file_exists(APP_PATH . 'Locale' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'App.php')) {
                require APP_PATH . 'Locale' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'App.php';
            }
        }
        if (!array_key_exists($language, $LANG)) {
            return $language;
        } else {
            $language = $LANG[$language];
            return $language;
        }
    }

    /**
     * Initialize cache driver;
     * 
     * @return type
     */
    private function _initCache() {
        static $cache = null;
        if (empty($cache)) {
            $cacheConfig = KantRegistry::get('config')->reference('cache');
            $cacheAdapter = 'default';
            try {
                $cache = KantFactory::getCache($cacheConfig)->getCache($cacheAdapter);
            } catch (RuntimeException $e) {
                if (!headers_sent()) {
                    header('HTTP/1.1 500 Internal Server Error');
                }
                exit('Load Cache Error: ' . $e->getMessage());
            }
        }
        return $cache;
    }

    /**
     * Load Cookie
     */
    private function _initCookie() {
        static $cookie = null;
        if (empty($cookie)) {
            $cookieConfig = KantFactory::getConfig()->reference('cookie');
            try {
                $cookie = Cookie\Cookie::getInstance($cookieConfig);
            } catch (RuntimeException $e) {
                if (!headers_sent()) {
                    header('HTTP/1.1 500 Internal Server Error');
                }
                exit('Load Cache Error: ' . $e->getMessage());
            }
            $this->cookie = $cookie;
        }
        return $cookie;
    }

    /**
     * 
     * Format Rest URL
     *
     * @param string $url
     * @param array $vars
     * @param string $suffix
     * @return string
     */
    public function url($url = '', $vars = '', $suffix = true) {
        $originalparams = array();
        $config = KantFactory::getConfig()->reference();
        if (strpos($url, $config['url_suffix']) !== false) {
            $url = rtrim($url, $config['url_suffix']);
        }
        $info = parse_url($url);
        if (isset($info['fragment'])) {
            $anchor = $info['fragment'];
            if (false !== strpos($anchor, '?')) {
                list($anchor, $info['query']) = explode('?', $anchor, 2);
            }
        }
        if (!empty($info['host'])) {
            return $url;
        }
        // 解析参数
        if (is_string($vars)) { // aaa=1&bbb=2 转换成数组
            parse_str($vars, $vars);
        } elseif (!is_array($vars)) {
            $vars = array();
        }
        if (isset($info['query'])) { // 解析地址里面参数 合并到vars
            parse_str($info['query'], $params);
            $vars = array_merge($params, $vars);
        }

        $depr = "/";
        $url = trim($url, $depr);
        $path = explode($depr, $url);
        $var['module'] = $path[0];
        $var['ctrl'] = !empty($path[1]) ? $path[1] : $config['route']['ctrl'];
        $var['act'] = !empty($path[2]) ? $path[2] : $config['route']['act'];
        if (!empty($path[3])) {
            $restpath = array_slice($path, 3);
            foreach ($restpath as $key => $val) {
                $arr = preg_split("/[,:=-]/", $val, 2);
                $originalparams[$arr[0]] = isset($arr[1]) ? $arr[1] : '';
            }
        }
        $url = APP_URL . implode($depr, ($var));
        $vars = array_merge($originalparams, $vars);
        if (!empty($vars)) { // 添加参数
            foreach ($vars as $var => $val) {
                if ('' !== trim($val)) {
//					$url .= $depr . $var . "," . urlencode($val);
                    $url .= $depr . $var . "," . $val;
                }
            }
        }
        //$url = rtrim($url, "/");
        if ($suffix) {
            $suffix = $suffix === true ? $config['url_suffix'] : $suffix;
            if ($pos = strpos($suffix, '|')) {
                $suffix = substr($suffix, 0, $pos);
            }
            //if ($suffix && '/' != substr($url, -1)) {
            if ($suffix) {
                $url .= $suffix;
            }
        }
        if (isset($anchor)) {
            $url .= '#' . $anchor;
        }
        return $url;
    }

    /**
     * Determine the ajax request
     * 
     * @return boolean
     */
    protected function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if ('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if (!empty($_POST['ajax']) || !empty($_GET['ajax'])) {
            return true;
        }
        return false;
    }

    public function widget($widgetname, $method, $data = array(), $return = false) {
        $dispatchInfo = KantRegistry::get('config')->reference('dispatchInfo');
        $module = isset($dispatchInfo['module']) ? ucfirst($dispatchInfo['module']) : '';
        $classname = ucfirst($widgetname) . 'Widget';
        if ($module) {
            $filepath = APP_PATH . 'Module' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Widget' . DIRECTORY_SEPARATOR . $classname . '.php';
        } else {
            $filepath = APP_PATH . 'Widget' . DIRECTORY_SEPARATOR . $classname . '.php';
        }
        if (file_exists($filepath)) {
            include_once $filepath;
            if (!class_exists($classname)) {
                throw new Exception("Class $classname does not exists");
            }
            if (!method_exists($classname, $method)) {
                throw new Exception("Method $method does not exists");
            }
            $widget = new $classname;
            $content = call_user_func_array(array($widget, $method), $data);
            if ($return) {
                return $content;
            } else {
                echo $content;
            }
        }
    }

    /**
     *  SET 
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }

    /**
     * GET
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return;
        }
    }

}
