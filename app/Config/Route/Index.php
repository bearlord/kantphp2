<?php

/**
 * Visit it on http://www.kantphp.com/index/welcome,
 * but not http://www.kantphp.com/welcome.
 * The routes has been group by module and it's been the index module
 */
$router->get("/welcome", function() {
    return "Welcome To Kant Framework V2.2";
});
