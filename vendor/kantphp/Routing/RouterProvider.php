<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Routing;

use Kant\Foundation\Component;

class RouterProvider extends Component {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $this->loadRoutes();
    }

    /**
     * Load the application routes.
     *
     * @return void
     */
    protected function loadRoutes() {
       
    }
    
    /**
     * Define routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    public function mapRoutes() {
        
    }

}
