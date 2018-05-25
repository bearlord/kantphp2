<?php
defined('KANT_DEBUG') or define('KANT_DEBUG', true);

//Application path
define('APP_PATH', __DIR__ . '/../app');

include __DIR__ . '/../vendor/kantphp/Framework.php';

$env = 'dev';

$config = require(APP_PATH . "/config/{$env}/config.php");

(new \Kant\Web\Application($config))->run();

?>
