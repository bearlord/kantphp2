<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Controller;

use Kant\Base;

!defined('IN_KANT') && exit('Access Denied');

/**
 * Base Controller 
 * 
 * @access public
 * @since version 1.0
 * @todo .etc
 */
class BaseController extends Base {

    protected $view;
    protected $dispatchInfo;

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
        $this->initView();
    }

    /**
     * initView
     * 
     * @return type
     */
    protected function initView() {
        if ($this->view == '') {
            $this->view = new \Kant\View\View();
        }
        return $this->view;
    }

    public function __call($method, $args) {
        $dispatchInfo = KantRegistry::get('dispatchInfo');
        if (0 === strcasecmp($method, strtolower($dispatchInfo['act']) . "Action")) {
            if (method_exists($this, '_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method, $args);
            } elseif (file_exists($this->view->parseTemplate())) {
                // 检查是否存在默认模版 如果有直接输出模版
                $this->display();
            } else {
                throw new KantException(sprintf("No action exists:%s", cfirst($dispatchInfo['act']) . 'Action'));
            }
        } else {
            throw new KantException("Method not exists");
        }
    }

}
