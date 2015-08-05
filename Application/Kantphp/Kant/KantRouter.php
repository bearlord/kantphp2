<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant;

!defined('IN_KANT') && exit('Access Denied');

class KantRouter extends Router {
    
}

class Router {

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

    public function __construct() {
        
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
        $tmp = explode('/', $pathInfo);
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
            if (strpos($action, ".") !== false) {
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
