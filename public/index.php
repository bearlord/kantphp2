<?php

use Kant\KantApplication;

//Application path
define('APP_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR);

include dirname(APP_PATH) . '/Kantphp/Framework.php';

KantApplication::getInstance('Development')->run();

?>
