<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

/**
 * Cli模式下调用
 * RUN AS php index.php "demo/clisleep"
 */
class ClisleepController extends BaseController {
    
    public function indexAction() {
        $i = 0;
        while ($i<=100) {
           $i++; 
           print "$i\n";
           sleep(1);
        }
    }
}
