<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Build;

use Kant\Registry\KantRegistry;
use Kant\KantFactory;

!defined('IN_KANT') && exit('Access Denied');

class Build {

    protected static $controller = '<?php
namespace [MODULE]\Controller;
use Kant\Controller\BaseController;
class [CONTROLLER]Controller extends BaseController {
    public function indexAction(){
        echo "Welcome to KantPHP Framework";
    }
}';

    public static function checkDir($module) {
        if (!is_dir(MODULE_PATH . $module)) {
            self::buildAppDir($module);
            self::buildController($module);
        }
    }

    public static function buildAppDir($module) {
        if (is_writeable(MODULE_PATH)) {
            $dirs = array(
                MODULE_PATH . $module . '/',
                MODULE_PATH . $module . '/Controller/',
                MODULE_PATH . $module . '/Model/',
                MODULE_PATH . $module . '/View/',
                MODULE_PATH . $module . '/Widget/',
            );
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            self::buildDirSecure($dirs);
        }
    }

    public static function buildDirSecure($dirs) {
        $config = KantRegistry::get('config');
        $dir_secure_filename = !empty($config['dir_secure_filename']) ? $config['dir_secure_filename'] : 'index.html';
        $files = explode(",", $dir_secure_filename);
        $content = !empty($config['dir_secure_content']) ? $config['dir_secure_content'] : '';
        foreach ($files as $filename) {
            foreach ($dirs as $dir) {
                file_put_contents($dir . $filename, $content);
            }
        }
    }

    /**
     * Build Controller
     * 
     * @param string $module
     * @param string $controller
     */
    static public function buildController($module, $controller = 'Index') {
        $file = MODULE_PATH . $module . '/Controller/' . $controller . 'Controller.php';
        if (!is_file($file)) {
            $content = str_replace(array('[MODULE]', '[CONTROLLER]'), array($module, $controller), self::$controller);
            file_put_contents($file, $content);
        }
    }

}
