<?php


//Application path
define('APP_PATH', __DIR__ . '/../app/');

include __DIR__ . '/../vendor/kantphp/Framework.php';

\Kant\Web\Application::getInstance('Dev')->run();

?>
