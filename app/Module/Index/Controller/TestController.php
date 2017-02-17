<?php

namespace App\Index\Controller;

use Kant\Kant;
use Kant\Http\Request;
use Kant\Http\Response;

class TestController extends \Kant\Controller\Controller {

    public function indexAction() {
        echo "index";
    }

    public function queryAction() {
        $query = new \Kant\Database\Query;
        // compose the query
        $row = $query->select('*')
                ->from('{{%posts}}')
                ->limit(10)
                ->one();
        var_dump($row);
    }

    public function modelAction(\Kant\Http\Request $request, \Kant\Http\Response $response) {

        $Posts = new \App\Index\Model\Posts();
        $Post = $Posts->find()->one();
        $Post = \App\Index\Model\Posts::find()->one();
        var_dump($Post);
        echo $Post->p_title;
        $Post->p_title = "abcd100";
        $Post->save();
        $this->view->display();
    }

    /**
     * 缓存测试
     */
    public function fcacheAction() {
        \Kant\Cache\Cache::instance();
        \Kant\Cache\Cache::set("a", 100);
        var_dump(\Kant\Cache\Cache::get("a"));
    }

    /**
     * 依赖注入
     * 
     * @param \Kant\Http\Request $request
     * @param \Kant\Http\Request $request
     * @param \stdClass $c
     * @param \stdClass $d
     * @param \stdClass $e
     * @param \stdClass $f
     * @param \stdClass $g
     * @param \stdClass $h
     */
    public function diAction(\Kant\Http\Request $request, \Kant\Http\Request $request, \stdClass $c, \stdClass $d, \stdClass $e, \stdClass $f, \stdClass $g, \stdClass $h) {
        var_dump($request->get('name'));
        var_dump($c);
        var_dump($d);
        var_dump($h);
    }

    /**
     * Request,Response测试
     * 
     * @param Request $request
     * @param Response $response
     */
    public function requestAction(Request $request, Response $response) {
        var_dump($request->all());
        $req = Request::capture();
        var_dump($req->all());
    }

    /**
     * 验证
     * 
     * @param Request $request
     * @param Response $response
     */
    public function validateAction(Request $request, Response $response) {
        if ($request->isMethod("post")) {
            $attrs = $request->all();
            $model = new \App\Index\Model\Posts();
            var_dump(['PostsModel' => $attrs]);
            $model->load(['PostsModel' => $attrs]);
            $res = $model->validate();
            var_dump($res);
            if ($res) {
                // 若所有输入都是有效的
            } else {
                // 有效性验证失败：$errors 属性就是存储错误信息的数组
                $errors = $model->errors;
                var_dump($errors);
            }
            
        }


        $this->view->display();
    }
    
    public function renderAction() {
//        $this->view->name = 'zhangsan';
        return $this->view->render('test/render', ['name' => 'zhangsan']);
    }

    /**
     * 缓存测试
     */
    public function cacheAction() {
        var_dump(Kant::$app->cache);
        Kant::$app->cache->set('hello', 'Hello World!!');
        $h = Kant::$app->cache->get('hello');
        var_dump($h);
    }
    
    public function cookieAction() {
        Kant::$app->cookie->set('name', 'hello');
        $ck = Kant::$app->cookie->get('name');
        var_dump($ck);
    }
}
