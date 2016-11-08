<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\View;

use Kant\Base;
use Kant\Registry\KantRegistry;
use Kant\Exception\KantException;
use Kant\KantFactory;

!defined('IN_KANT') && exit('Access Denied');

/**
 * View class
 * @access public
 * @version 1.1
 * @since version 1.0 
 */
class View extends Base {

    /**
     * template theme
     * 
     * @var string 
     */
    protected $theme = 'default';

    /**
     *
     * @var string 
     */
    protected $templateSuffix = '.php';

    /**
     * dispatchInfo
     * 
     * @var string 
     */
    protected $dispatchInfo;

    /**
     *
     * @var object 
     */
    private static $_instance;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * template variables
     * 
     * @var type 
     */
    protected $tvar = array();

    public function __construct() {
        parent::__construct();
        $this->dispatchInfo = KantRegistry::get('dispatchInfo');
    }

    /**
     * 
     * @param type $key
     * @return type
     */
    public function __get($key) {
        if (isset($this->$key)) {
            return($this->$key);
        } else {
            return(NULL);
        }
    }

    /**
     * 
     * @param type $key
     * @param type $value
     */
    public function __set($key, $value) {
        $this->$key = $value;
        $this->tvar[$key] = $value;
    }

    /**
     * Parse template path
     */
    public function parseTemplate($template = '') {
        if (is_file($template)) {
            return $template;
        }
        $config = KantRegistry::get('config')->reference();
        $tpldir = $this->getTplDir();
        if (empty($template)) {
            $tplfile = $tpldir . strtolower($this->dispatchInfo[1]) . DIRECTORY_SEPARATOR . strtolower($this->dispatchInfo[2]) . $config['template_suffix'];
        } else {
            $tplfile = $tpldir . $template . $config['template_suffix'];
        }
        return $tplfile;
    }

    /**
     * Display template
     * 
     * @param string $template
     * @throws RuntimeException
     */
    public function display($template = '') {
        $content = $this->fetch($template);
        header("X-Powered-By:KantPHP Framework");
        echo $content;
    }

    /**
     * Fetach template
     * 
     * @param type $template
     * @return type
     * @throws RuntimeException
     */
    public function fetch($template) {
        extract($this->tvar, EXTR_OVERWRITE);
        $tplfile = $this->parseTemplate($template);
        ob_start();
        ob_implicit_flush(0);
        if (file_exists($tplfile)) {
            include_once $tplfile;
        }
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Include template
     * 
     * @param type $template
     * @param type $module
     * @throws RuntimeException
     */
    public function layout($template, $module = '') {
        if (empty($template)) {
            return;
        }
        list($ctrl, $act) = explode("/", strtolower($template));
        $tpldir = $this->getTplDir($module);
        $tplfile = $tpldir . $ctrl . DIRECTORY_SEPARATOR . $act . '.php';
        if (!file_exists($tplfile)) {
            $debug = KantRegistry::get('config')->get('debug');
            if ($debug) {
                throw new KantException(sprintf("No template: %s", $tplfile));
            } else {
                $this->redirect($this->lang('system_error'), 'close');
            }
        }
        include_once $tplfile;
    }

    /**
     * Get Tpl Dir
     */
    protected function getTplDir($module = '') {
        if ($module == '') {
            $module = isset($this->dispatchInfo[0]) ? strtolower($this->dispatchInfo[0]) : '';
        }
        $theme = KantRegistry::get('config')->get('theme');
        if ($module) {
            $tpldir = TPL_PATH . $theme . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
        } else {
            $tpldir = TPL_PATH . $theme . DIRECTORY_SEPARATOR;
        }
        return $tpldir;
    }

}
