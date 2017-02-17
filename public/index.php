<?php

use Kant\KantApplication;

//Application path
define('APP_PATH', __DIR__ . '/../app/');

include __DIR__ . '/../vendor/kantphp/Framework.php';

KantApplication::getInstance('Development')->run();

?>
