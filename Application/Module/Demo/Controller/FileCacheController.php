<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

/**
 * 缓存
 */
class FileCacheController extends BaseController {

    protected $cacheAdapter = 'file';

    public function indexAction() {
        $this->setcacheAction();
    }

    /**
     * 设置缓存数据
     */
    public function setcacheAction() {
        $foo = "Hello World";
        $arr = array(
            'name' => '张三',
            'email' => 'admin@localhost.com'
        );

        $this->cache->set('foo', $foo);
        $this->cache->set('arr', $arr);
    }

    /**
     * 获取缓存数据
     */
    public function getcacheAction() {
        $foo = $this->cache->get('foo');
        $arr = $this->cache->get('arr');
        var_dump($foo);
        var_dump($arr);
    }

}

?>
