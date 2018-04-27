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

$router->get("/welcome", function() {
	return "Welcome To Kant Framework V2.2";
});
$router->get("/age", function() {
	\Kant\Kant::$app->cache->set('hoo', 'hello world!!');

	$get = Kant\Kant::$app->request->all();
	var_dump($get);
	return "Welcome To Kant Framework V2.2";
})->middleware([App\Middleware\CheckAge::class]);



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
})->where('id', '[0-9]+');
