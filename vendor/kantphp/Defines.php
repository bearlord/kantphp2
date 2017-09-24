<?php

// App path
if (! defined('APP_PATH')) {
    define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR);
}

// Storage directory
define('RUNTIME_PATH', APP_PATH . 'Runtime' . DIRECTORY_SEPARATOR);
// Template directory
define('TPL_PATH', APP_PATH . 'View' . DIRECTORY_SEPARATOR);
// Config directroy
define('CFG_PATH', APP_PATH . 'Config' . DIRECTORY_SEPARATOR);
define('MODULE_PATH', APP_PATH . 'Module' . DIRECTORY_SEPARATOR);
// Libary directory
define('LIB_PATH', APP_PATH . 'Library' . DIRECTORY_SEPARATOR);
// Public Path
define('PUBLIC_PATH', dirname(APP_PATH) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

/**
 * This constant defines whether error handling should be enabled.
 * Defaults to true.
 */
defined('ENABLE_ERROR_HANDLER') or define('ENABLE_ERROR_HANDLER', true);
defined('KANT_BEGIN_TIME') or define('KANT_BEGIN_TIME', microtime(true));

// Log path
if (! defined('LOG_PATH')) {
    define('LOG_PATH', RUNTIME_PATH . 'logs/');
}

// Web root
if (! defined('APP_URL')) {
    define('APP_URL', substr(dirname($_SERVER['SCRIPT_NAME']), - 1, 1) == '/' ? dirname($_SERVER['SCRIPT_NAME']) : trim(dirname($_SERVER['SCRIPT_NAME']), "\\") . '/');
}

define('CHARSET', 'utf-8');

require_once KANT_PATH . 'Function/Global.php';
require_once APP_PATH . 'Function/Common.php';

ini_set('magic_quotes_runtime', 0);
if (get_magic_quotes_gpc() == false) {
    array_walk_recursive($_POST, "addslashess");
    array_walk_recursive($_GET, "addslashess");
    array_walk_recursive($_COOKIE, "addslashess");
}

