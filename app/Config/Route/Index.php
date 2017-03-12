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


$router->get('user/{id}', function ($id) {
    return 'User ' . $id;
});


/**
 * Controller
 */
$router->get("/test104", "HomeController@index");


$router->get("/test106", ['namespace' => 'Index'], "HomeController@index");


