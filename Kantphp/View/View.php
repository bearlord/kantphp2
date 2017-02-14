<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\View;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Registry\KantRegistry;
use Kant\KantFactory;
use Kant\Exception\KantException;

/**
 * View class
 * @access public
 * @version 1.1
 * @since version 1.0
 */
class View extends Component {

    /**
     * template theme
     *
     * @var string
     */
    protected $theme = 'default';

    /**
     * dispatchInfo
     *
     * @var string
     */
    protected $dispatchInfo;

    /**
     * template variables
     *
     * @var type
     */
    protected $params = array();

    /**
     * @var array a list of available renderers indexed by their corresponding supported file extensions.
     * Each renderer may be a view renderer object or the configuration for creating the renderer object.
     * For example, the following configuration enables both Smarty and Twig view renderers:
     * If no renderer is available for the given view file, the view file will be treated as a normal PHP
     * and rendered via [[renderPhpFile()]].
     */
    public $renderers;

    /**
     * @var null|string|false the name of the layout to be applied to this controller's views.
     * This property mainly affects the behavior of [[render()]].
     * If false, no layout will be applied.
     */
    public $layout = 'main';

    /**
     *
     * @var string the root directory that contains layout view files for this module.
     */
    private $_layoutPath;

    /**
     *
     */
    public function __construct() {
        parent::__construct();
        $this->dispatchInfo = KantRegistry::get('dispatchInfo');
    }

    /**
     * Returns the value of a property.
     *
     * @param type $key
     * @return type
     */
    public function __get($key) {
        if (isset($this->params[$key])) {
            return($this->params[$key]);
        } else {
            return(NULL);
        }
    }

    /**
     * Sets the value of a property.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value) {
        $this->params[$key] = $value;
    }

    /**
     * Display template
     *
     * @param string $view
     * @throws RuntimeException
     */
    public function display($view = '') {
        $content = $this->fetch($view);
        header("X-Powered-By:KantPHP Framework");
        echo $content;
    }

    /**
     * Fetach template
     *
     * @param type $view
     * @return type
     * @throws RuntimeException
     */
    public function fetch($view, $params = []) {
        $viewFile = $this->findViewFile($view);
        $params = array_merge($this->params, $params);
        return $this->renderFile($viewFile, $params);
    }

    /**
     * Renders a view and applies layout if available.
     *
     * @param type $view
     */
    public function render($view = "", $params = []) {
        $layoutFile = $this->findLayoutFile();
        $content = $this->fetch($view, $params);
        if ($layoutFile !== false) {
            return $this->renderFile($layoutFile, ['content' => $content]);
        } else {
            return $content;
        }
    }

    /**
     * Finds the view file based on the given view name.
     *
     * @param string $view
     */
    public function findViewFile($view = '') {
        if (is_file($view)) {
            return $view;
        }
        $ext = KantFactory::getConfig()->get("view.ext");
        $viewPath = $this->getViewPath();
        if (empty($view)) {
            $viewFile = $viewPath . strtolower($this->dispatchInfo[1]) . DIRECTORY_SEPARATOR . strtolower($this->dispatchInfo[2]) . $ext;
        } else {
            $viewFile = $viewPath . $view . $ext;
        }
        return $viewFile;
    }

    /**
     * Finds the applicable layout file.
     * 
     * @param type $view
     * @return boolean|string
     */
    public function findLayoutFile() {
        if (is_string($this->layout)) {
            $layout = $this->layout;
        }
        $ext = KantFactory::getConfig()->get("view.ext");
        if (strncmp($layout, '/', 1) === 0) {
            $file = $this->getLayoutPath() . DIRECTORY_SEPARATOR . substr($layout, 1) . $ext;
        } else {
            $file = $this->getLayoutPath() . DIRECTORY_SEPARATOR . $layout . $ext;
        }
        return $file;
    }

    /**
     * Renders a view file.
     */
    public function renderFile($viewFile, $params = []) {
        $ext = pathinfo($viewFile, PATHINFO_EXTENSION);
        if (isset($this->renderers[$ext])) {
            if (is_array($this->renderers[$ext]) || is_string($this->renderers[$ext])) {
                $class = ucfirst(KantFactory::getConfig()->get($this->renderers[$ext]));
                $this->renderers[$ext] = Kant::createObject(["class" => "Kant\\View\\$class"]);
            }
            /* @var $renderer ViewRenderer */
            $renderer = $this->renderers[$ext];
            $output = $renderer->render($this, $viewFile, $params);
        } else {
            $output = $this->renderPhpFile($viewFile, $params);
        }
        return $output;
    }

    /**
     * Renders a view file.
     */
    public function renderPhpFile($file, $params = []) {
        ob_start();
        ob_implicit_flush(0);
        extract($params, EXTR_OVERWRITE);
        if (file_exists($file)) {
            include_once $file;
        }
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Get view path
     */
    protected function getViewPath($module = '') {
        if ($module == '') {
            $module = isset($this->dispatchInfo[0]) ? strtolower($this->dispatchInfo[0]) : '';
        }
        $theme = KantFactory::getConfig()->get('view.theme');
        if ($module) {
            $viewPath = TPL_PATH . $theme . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
        } else {
            $viewPath = TPL_PATH . $theme . DIRECTORY_SEPARATOR;
        }
        return $viewPath;
    }

    /**
     * Returns the directory that contains layout view files for this module.
     * 
     * @return string
     */
    protected function getLayoutPath() {
        if ($this->_layoutPath === null) {
            $this->_layoutPath = $this->getViewPath() . 'layouts';
        }
        return $this->_layoutPath;
    }

}
