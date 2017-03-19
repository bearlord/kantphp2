<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
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

}
