<?php
/**
 * Visit it on http://www.kantphp.com/api/welcome,
 * but not http://www.kantphp.com/welcome.
 * The routes has been group by module and it's been the index module
 */
$router->get("/welcome", function() {
    return "Welcome To Kant Framework V2.2";
});

/**
 * The Restful API, the usage's like Laravel. as:
 * GET  http://www.kantphp.com/api/photo
 * GET  http://www.kantphp.com/api/photo/654
 * POST http://www.kantphp.com/api/photo/654
 * ...
 */
$router->resource('photo', 'PhotoController');


