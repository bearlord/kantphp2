<?php


//Application path
define('APP_PATH', __DIR__ . '/../app');

include __DIR__ . '/../vendor/kantphp/Framework.php';

$env = 'dev';

$config = require(APP_PATH . "/config/{$env}/config.php");

\Kant\Web\Application::getInstance($config)->run();

?>
