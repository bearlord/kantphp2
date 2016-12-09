<?php

namespace App\Index\Controller;

class TestController extends \Kant\Controller\Controller {

    public function indexAction() {
        echo "index";
    }

    public function modelAction() {
//        $PostsModel = new \App\Index\Model\PostsModel();
//        $row = $PostsModel->select("")->limit(10)->all();
//        var_dump($row);
        $query = new \Kant\Database\Query;
        // compose the query
        $row = $query->select('*')
                ->from('{{%posts}}')
                ->limit(10)
                ->one();
        var_dump($row);
    }

    public function cacheAction() {
        \Kant\Cache\Cache::platform();
        \Kant\Cache\Cache::set("a", 100);
        var_dump(\Kant\Cache\Cache::get("a"));
    }

}
