<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Controller;

use Kant\Foundation\Component;
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
class Controller extends Component {

    use \Kant\Traits\UrlTrait,
        \Kant\Traits\LangTrait,
        \Kant\Traits\WidgetTrait;

    /**
     *
     * @var type 
     */
    protected $view;
    
    /**
     *
     * @var type 
     */
    protected $dispatchInfo;
    
    /**
     * Layout
     */
    
    public $layout = 'main';


    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
        $this->initView();
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    /**
     * initialize
     */
    protected function initialize() {
        
    }

    /**
     * initView
     * 
     * @return type
     */
    protected function initView() {
        if ($this->view == '') {
            $this->view = \Kant\Kant::createObject([
                'class' => 'Kant\\View\\View',
                'layout' => $this->layout
            ]);
        }
        return $this->view;
    }

    /**
     * Magic call
     * 
     * @param type $method
     * @param type $args
     * @throws KantException
     */
    public function __call($method, $args) {
        $dispatchInfo = KantRegistry::get('dispatchInfo');
        if (0 === strcasecmp($method, strtolower($dispatchInfo[2]) . "Action")) {
            if (method_exists($this, '_empty')) {
                $this->_empty($method, $args);
            } elseif (file_exists($this->view->findViewFile())) {
                $this->display();
            } else {
                throw new KantException(sprintf("No action exists:%s", ucfirst($dispatchInfo[2]) . 'Action'));
            }
        } else {
            throw new KantException("Method not exists");
        }
    }

}
