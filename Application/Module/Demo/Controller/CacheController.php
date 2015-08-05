<?php

namespace Module\Demo\Controller;

use Kant\Controller\BaseController;
use Kant\Runtime\Runtime;

class CacheController extends BaseController {

//	protected $cacheAdapter = 'redis';

    public function indexAction() {
        $this->cache->set("var", "123");
        $this->cache->set("xu", array(
            'name' => "徐",
            'sex' => 'male'
        ));
        echo $this->cache->get("var");
        echo '<br />';
        print_r($this->cache->get("xu"));
        echo '<br />';
        highlight_file(__FILE__);
        $tmp = str_repeat('hello', 10000000);
        Runtime::mark('end');
        $usage = Runtime::calculate();
        var_dump($usage);
    }

}

?>
