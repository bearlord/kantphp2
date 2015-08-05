<?php

use Kant\Kant;

//Application path
define('APP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR);

include APP_PATH . 'Kantphp/Kant.php';

Kant::createApplication('Development')->boot();
?>
