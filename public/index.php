<?php

use Kant\KantFactory;

//Application path
define('APP_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR);

include dirname(APP_PATH) . '/Kantphp/Framework.php';

define("CREATE_MODULE", "Index");

$app = KantFactory::getApplication('Development');
$app->boot();
?>
