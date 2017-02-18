<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Controller;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Exception\KantException;
use Kant\Registry\KantRegistry;

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
    protected $dispatcher;
    
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
            $this->view = Kant::createObject([
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
        $dispatcher = KantRegistry::get('dispatcher');
        if (0 === strcasecmp($method, strtolower($dispatcher[2]) . "Action")) {
            if (method_exists($this, '_empty')) {
                $this->_empty($method, $args);
            } elseif (file_exists($this->view->findViewFile())) {
                $this->display();
            } else {
                throw new KantException(sprintf("No action exists:%s", ucfirst($dispatcher[2]) . 'Action'));
            }
        } else {
            throw new KantException("Method not exists");
        }
    }

}
