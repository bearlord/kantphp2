<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
// KantPHP Path
define('KANT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
// Vendor Path
define('VENDOR_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once KANT_PATH . 'Defines.php';
require_once VENDOR_PATH . 'autoload.php';
require_once KANT_PATH . 'Loader/Autoload.php';

\Kant\Kant::$container = new \Kant\Di\Container();


