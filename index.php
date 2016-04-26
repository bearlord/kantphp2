<?php

use Kant\KantFactory;

//Application path
define('APP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR);

include APP_PATH . 'Kantphp/Framework.php';

$app = KantFactory::getApplication('Development');
$app->boot();
?>
