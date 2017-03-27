<?php

/**
 * Get
 */
$router->get("/test102", function() {
    return strtoupper("hello world!");
    return "Welcome To Kant Framework V2.1 - 102 - GET";
});

/**
 * Post
 */
$router->post("/test103", function() {
    return "Welcome To Kant Framework V2.1 - 103 - POST";
});


$router->get('user/{id}/{name?}', ['as' => 'myuser', function ($id, $name="") {
    return 'User ' . $id . "-" . $name;
}]);

/**
 * Controller
 */
$router->get("/test104", "HomeController@index");


$router->get("/test106", ['namespace' => 'Index'], "HomeController@index");

$router->resource('photo', 'PhotoController');


$router::get('userinfo/{id}/{name}', function($id, $name)
{
    return $id . $name;
})
->where(array('id' => '[0-9]+', 'name' => '[a-z]+'));
