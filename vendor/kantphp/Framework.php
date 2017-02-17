<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
//KantPHP Path
define('KANT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require_once KANT_PATH . 'Defines.php';
require_once KANT_PATH . 'Loader/Autoload.php';

\Kant\Kant::$container = new \Kant\Di\Container();


