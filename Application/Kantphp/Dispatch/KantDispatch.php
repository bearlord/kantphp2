<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Dispatch;

use Kant\Log\Log;
use Kant\Registry\KantRegistry;
use Kant\KantFactory;

!defined('IN_KANT') && exit('Access Denied');

class KantDispatch extends Dispatch {
    
}

class Dispatch {

    private static $_instance;
    private $_dispatchInfo;
    private $_pathInfo;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Set path info
     *
     * @param string $pathinfo
     * @return Cola
     */
    public function setPathInfo($pathinfo = null) {
        if (null === $pathinfo) {
            $config = KantRegistry::get('config');
            if ($config['path_info_repair'] == false) {
                $pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
                $pathinfo = str_replace($config['url_suffix'], '', $pathinfo);
            } else {
                foreach (array('REQUEST_URI', 'HTTP_X_REWRITE_URL', 'argv') as $var) {
                    if (!empty($_SERVER[$var])) {
                        $requestUri = $_SERVER[$var];
                        if ($var == 'argv') {
                            $requestUri = @strtolower($requestUri[1]);
                        }
                        break;
                    }
//                    if ($requestUri = $_SERVER[$var]) {
//                        if ($var == 'argv') {
//                            $requestUri = strtolower($requestUri[1]);
//                        }
//                        break;
//                    }
                }
                $requestUri = str_replace($config['url_suffix'], '', ltrim($requestUri, '/'));
                $scriptName = strtolower(ltrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
                //url as [/index.php?module=demo&ctrl=index&act=index] or [/index.php/demo/index/index]
                if (strpos($requestUri, "index.php") !== false) {
                    $parse = parse_url($requestUri);
                    //url as [/index.php?module=demo&ctrl=index&act=index] 
                    if (!empty($parse['query']) && strpos($parse['query'], 'module') !== false) {
                        $pathinfo = "";
                    } else {
                        //url as [/index.php/demo/index/index]
                        $pathinfo = ltrim(str_replace($scriptName, '', $requestUri));
                        if (strpos($pathinfo, "index.php/") !== false) {
                            $pathinfo = str_replace("index.php/", "", $pathinfo);
                        }
                    }
                } else {
                    //url as [/demo/index/index]
                    $pathinfo = ltrim(str_replace($scriptName, '', $requestUri));
                }
            }
        }
        return $pathinfo;
    }

    /**
     * Get path info
     *
     * @return string
     */
    public function getPathInfo() {
        if (null === $this->_pathInfo) {
            $this->_pathInfo = $this->setPathInfo();
        }
        return $this->_pathInfo;
    }

    /**
     * Set dispatch info
     *
     * @param array $dispatchInfo
     * @return Cola
     */
    public function setDispatchInfo($dispatchInfo = null) {
        if (null === $dispatchInfo) {
            $config = KantRegistry::get('config');
            $pathinfo = $this->getPathInfo();
            if (!empty($pathinfo)) {
                $router = KantFactory::getRoute();
                $router->setUrlSuffix($config['url_suffix']);
                $router->add($config['route_rules']);
                $router->enableDynamicMatch(true, $config['route']);
                $dispatchInfo = $router->match($pathinfo);
            } else {
                $dispatchInfo['module'] = empty($_GET['module']) ? $config['route']['module'] : $_GET['module'];
                $dispatchInfo['ctrl'] = empty($_GET['ctrl']) ? $config['route']['ctrl'] : $_GET['ctrl'];
                $dispatchInfo['act'] = empty($_GET['act']) ? $config['route']['act'] : $_GET['act'];
            }
            $merge = array_merge($dispatchInfo, $_GET);
            $dispatchInfo = $_GET = $merge;
            KantRegistry::set('dispatchInfo', $dispatchInfo);
        }
        return $dispatchInfo;
    }

    /**
     * Get dispatch info
     *
     * @return array
     */
    public function getDispatchInfo() {
        if (null === $this->_dispatchInfo) {
            $this->_dispatchInfo = $this->setDispatchInfo();
        }
        return $this->_dispatchInfo;
    }

}
