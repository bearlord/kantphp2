<?php

/**
 * 缓存
 */
class MemCacheController extends BaseController {

    //缓存类型，可以是file,memcache,redis.
    protected $cacheAdapter = 'memcache';

    public function __construct() {
        $this->loadCache();
    }

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

        $this->cache->set('foo', $foo, 60);
        $this->cache->set('arr', $arr, 120);
    }

    /**
     * 获取缓存数据
     */
    public function getcacheAction() {
        $foo = $this->cache->get('foo');
        $arr = $this->cache->get('arr');
        $ep = $this->cache->getExpire('foo');
        var_dump($foo);
        var_dump($arr);
        var_dump($ep - time());
    }

}

?>
