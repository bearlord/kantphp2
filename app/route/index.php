<?php

/**
 * Visit it on http://www.kantphp.com/index/welcome,
 * but not http://www.kantphp.com/welcome.
 * The routes has been group by module and it's been the index module
 */
use Kant\Kant;
use Kant\Routing\Router;
use Kant\Database\Query;
use Kant\Http\Request;
use Kant\Http\Response;
use Kant\View\View;

$router->get("/welcome", function() {
    $a = 10 / 0;
    return "Welcome To Kant Framework V2.2";
});

Router::get('/order/{id}', function($id, Query $query, Response $response) {
    $response->format = Response::FORMAT_JSON;

    $item = $query->from("p_orders")
                    ->where([
                        'id' => $id
                    ])->one();
    return [
        'status' => 200,
        'message' => '获取信息成功',
        'data' => $item
    ];
})->middleware('checkage:18')->where('id', '[0-9]+');


Router::get('/test', function(View $view, Response $response) {
    $users = [
        [
            'name' => '张三',
            'age' => 16,
            'avators' => [
                'http://www.qqxoo.com/uploads/allimg/161020/1356023128-13.jpg',
                'http://img3.a0bi.com/upload/ttq/20140813/1407915088900.jpg'
            ]
        ],
        [
            'name' => '李四',
            'age' => 18,
            'avators' => [
                'http://www.qqxoo.com/uploads/allimg/161020/1356023128-13.jpg',
                'http://img3.a0bi.com/upload/ttq/20140813/1407915088900.jpg'
            ]
        ],
    ];
    
//    $view->layout = false;
//    $response->format = Response::FORMAT_JSON;
    
    return $view->render('index/test/user', [
                'users' => $users
    ]);
});

Router::get('template', ['uses'=>'TestController@template']);

$router->resource('prefix/foos', 'FooController');

Router::resource('photos', 'PhotoCommentController');
Router::resource('photos.comments', 'PhotoCommentController');
