<?php

/**
 * Visit it on http://www.kantphp.com/index/welcome,
 * but not http://www.kantphp.com/welcome.
 * The routes has been group by module and it's been the index module
 */
use Kant\Kant;
use Kant\Routing\Router;
use Kant\Database\Query;
use Kant\Http\Response;
use Kant\View\View;

$router->get("/welcome", function() {
    $a = 10 /0;
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
//    return 'hello world!!';
    return $view->render('index/test/template', [

    ]);
});