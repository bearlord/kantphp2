<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Controller;

use Kant\Foundation\Base;
use Kant\Exception\KantException;
use Kant\Registry\KantRegistry;
use Kant\View\View;

/**
 * Base Controller 
 * 
 * @access public
 * @since version 1.0
 * @todo .etc
 */
class Controller extends Base {

    protected $view;
    protected $dispatchInfo;

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
        $this->initView();
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    protected function _initialize() {
        
    }

    /**
     * initView
     * 
     * @return type
     */
    protected function initView() {
        if ($this->view == '') {
            $this->view = View::getInstance();
        }
        return $this->view;
    }

    public function __call($method, $args) {
        $dispatchInfo = KantRegistry::get('dispatchInfo');
        if (0 === strcasecmp($method, strtolower($dispatchInfo[2]) . "Action")) {
            if (method_exists($this, '_empty')) {
                $this->_empty($method, $args);
            } elseif (file_exists($this->view->parseTemplate())) {
                $this->display();
            } else {
                throw new KantException(sprintf("No action exists:%s", ucfirst($dispatchInfo[2]) . 'Action'));
            }
        } else {
            throw new KantException("Method not exists");
        }
    }

}
