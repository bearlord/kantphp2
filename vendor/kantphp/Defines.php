<?php


/**
 * Gets the application start timestamp.
 */
defined('KANT_BEGIN_TIME') or define('KANT_BEGIN_TIME', microtime(true));

// App path
if (! defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/../../app'));
}

// Template directory
define('TPL_PATH', APP_PATH . '/view/');
// Config directroy
define('CFG_PATH', APP_PATH . '/config/');
define('MODULE_PATH', APP_PATH . '/Module/');
// Libary directory
define('LIB_PATH', APP_PATH . '/Library/');
// Public Path
define('PUBLIC_PATH', dirname(APP_PATH)  . '/public/');


// Web root
if (! defined('APP_URL')) {
    define('APP_URL', substr(dirname($_SERVER['SCRIPT_NAME']), - 1, 1) == '/' ? dirname($_SERVER['SCRIPT_NAME']) : trim(dirname($_SERVER['SCRIPT_NAME']), "\\") . '/');
}

require_once KANT_PATH . '/Function/Global.php';
