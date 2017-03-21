<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\View;

use Kant\Kant;
use Kant\Factory;
use Kant\Helper\Html;

/**
 * View class
 * @access public
 * @version 1.1
 * @since version 1.0
 */
class View extends BaseView {

    /**
     * @event Event an event that is triggered by [[beginBody()]].
     */
    const EVENT_BEGIN_BODY = 'beginBody';

    /**
     * @event Event an event that is triggered by [[endBody()]].
     */
    const EVENT_END_BODY = 'endBody';

    /**
     * The location of registered JavaScript code block or files.
     * This means the location is in the head section.
     */
    const POS_HEAD = 1;

    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the beginning of the body section.
     */
    const POS_BEGIN = 2;

    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the end of the body section.
     */
    const POS_END = 3;

    /**
     * The location of registered JavaScript code block.
     * This means the JavaScript code block will be enclosed within `jQuery(document).ready()`.
     */
    const POS_READY = 4;

    /**
     * The location of registered JavaScript code block.
     * This means the JavaScript code block will be enclosed within `jQuery(window).load()`.
     */
    const POS_LOAD = 5;

    /**
     * This is internally used as the placeholder for receiving the content registered for the head section.
     */
    const PH_HEAD = '<![CDATA[KANT-BLOCK-HEAD]]>';

    /**
     * This is internally used as the placeholder for receiving the content registered for the beginning of the body section.
     */
    const PH_BODY_BEGIN = '<![CDATA[KANT-BLOCK-BODY-BEGIN]]>';

    /**
     * This is internally used as the placeholder for receiving the content registered for the end of the body section.
     */
    const PH_BODY_END = '<![CDATA[KANT-BLOCK-BODY-END]]>';
    
    /**
     * @var AssetBundle[] list of the registered asset bundles. The keys are the bundle names, and the values
     * are the registered [[AssetBundle]] objects.
     * @see registerAssetBundle()
     */
    public $assetBundles = [];

    /**
     * @var array the registered meta tags.
     * @see registerMetaTag()
     */
    public $metaTags;

    /**
     * @var array the registered link tags.
     * @see registerLinkTag()
     */
    public $linkTags;

    /**
     * @var array the registered CSS code blocks.
     * @see registerCss()
     */
    public $css;

    /**
     * @var array the registered CSS files.
     * @see registerCssFile()
     */
    public $cssFiles;

    /**
     * @var array the registered JS code blocks
     * @see registerJs()
     */
    public $js;

    /**
     * template theme
     *
     * @var string
     */
    protected $theme = 'default';

    /**
     * dispatcher
     *
     * @var string
     */
    protected $dispatcher;

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
    private $_assetManager;


    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }
    /**
     * Marks the position of an HTML head section.
     */
    public function head() {
        echo self::PH_HEAD;
    }

    /**
     * Marks the beginning of an HTML body section.
     */
    public function beginBody() {
        echo self::PH_BODY_BEGIN;
        $this->trigger(self::EVENT_BEGIN_BODY);
    }

    /**
     * Marks the ending of an HTML body section.
     */
    public function endBody() {
        $this->trigger(self::EVENT_END_BODY);
        echo self::PH_BODY_END;

//        foreach (array_keys($this->assetBundles) as $bundle) {
//            $this->registerAssetFiles($bundle);
//        }
    }

    /**
     * Marks the ending of an HTML page.
     * @param boolean $ajaxMode whether the view is rendering in AJAX mode.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     */
    public function endPage($ajaxMode = false) {
        $this->trigger(self::EVENT_END_PAGE);

        $content = ob_get_clean();

        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode),
        ]);

        $this->clear();
    }

    /**
     * Clears up the registered meta tags, link tags, css/js scripts and files.
     */
    public function clear() {
        $this->metaTags = null;
        $this->linkTags = null;
        $this->css = null;
        $this->cssFiles = null;
        $this->js = null;
        $this->jsFiles = null;
        $this->assetBundles = [];
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
    public function fetch($view = '', $params = []) {
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
        $content = $this->fetch($view, $params);
        $layoutFile = $this->findLayoutFile();
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
        $ext = Factory::getConfig()->get("view.ext");
        $viewPath = $this->getViewPath();
        if (empty($view)) {
            $viewFile = $viewPath . strtolower($this->dispatcher[1]) . DIRECTORY_SEPARATOR . strtolower($this->dispatcher[2]) . $ext;
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
        $ext = Factory::getConfig()->get("view.ext");
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
                $class = ucfirst(Factory::getConfig()->get($this->renderers[$ext]));
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
            $module = isset($this->dispatcher[0]) ? strtolower($this->dispatcher[0]) : '';
        }
        $theme = Factory::getConfig()->get('view.theme');
        if ($module) {
            $viewPath = TPL_PATH . $theme . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
        } else {
            $viewPath = TPL_PATH . $theme . DIRECTORY_SEPARATOR;
        }
        return $viewPath;
    }

    /**
     * Registers the asset manager being used by this view object.
     * @return \yii\web\AssetManager the asset manager. Defaults to the "assetManager" application component.
     */
    public function getAssetManager() {
        return $this->_assetManager ?: Kant::$app->getAssetManager();
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

    /**
     * Registers the named asset bundle.
     * All dependent asset bundles will be registered.
     * @param string $name the class name of the asset bundle (without the leading backslash)
     * @param integer|null $position if set, this forces a minimum position for javascript files.
     * This will adjust depending assets javascript file position or fail if requirement can not be met.
     * If this is null, asset bundles position settings will not be changed.
     * See [[registerJsFile]] for more details on javascript position.
     * @return AssetBundle the registered asset bundle instance
     * @throws InvalidConfigException if the asset bundle does not exist or a circular dependency is detected
     */
    public function registerAssetBundle($name, $position = null) {
        if (!isset($this->assetBundles[$name])) {
            $am = $this->getAssetManager();
            $bundle = $am->getBundle($name);
            $this->assetBundles[$name] = false;
            // register dependencies
            $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
            foreach ($bundle->depends as $dep) {
                $this->registerAssetBundle($dep, $pos);
            }
            $this->assetBundles[$name] = $bundle;
        } elseif ($this->assetBundles[$name] === false) {
            throw new InvalidConfigException("A circular dependency is detected for bundle '$name'.");
        } else {
            $bundle = $this->assetBundles[$name];
        }

        if ($position !== null) {
            $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
            if ($pos === null) {
                $bundle->jsOptions['position'] = $pos = $position;
            } elseif ($pos > $position) {
                throw new InvalidConfigException("An asset bundle that depends on '$name' has a higher javascript file position configured than '$name'.");
            }
            // update position for all dependencies
            foreach ($bundle->depends as $dep) {
                $this->registerAssetBundle($dep, $pos);
            }
        }

        return $bundle;
    }

    /**
     * Registers a JS code block.
     * @param string $js the JS code block to be registered
     * @param integer $position the position at which the JS script tag should be inserted
     * in a page. The possible values are:
     *
     * - [[POS_HEAD]]: in the head section
     * - [[POS_BEGIN]]: at the beginning of the body section
     * - [[POS_END]]: at the end of the body section
     * - [[POS_LOAD]]: enclosed within jQuery(window).load().
     *   Note that by using this position, the method will automatically register the jQuery js file.
     * - [[POS_READY]]: enclosed within jQuery(document).ready(). This is the default value.
     *   Note that by using this position, the method will automatically register the jQuery js file.
     *
     * @param string $key the key that identifies the JS code block. If null, it will use
     * $js as the key. If two JS code blocks are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerJs($js, $position = self::POS_READY, $key = null) {
        $key = $key ?: md5($js);
        $this->js[$position][$key] = $js;
        return;
        if ($position === self::POS_READY || $position === self::POS_LOAD) {
            JqueryAsset::register($this);
        }
    }

    /**
     * Renders the content to be inserted in the head section.
     * The content is rendered using the registered meta tags, link tags, CSS/JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderHeadHtml() {
        $lines = [];
        if (!empty($this->metaTags)) {
            $lines[] = implode("\n", $this->metaTags);
        }

        if (!empty($this->linkTags)) {
            $lines[] = implode("\n", $this->linkTags);
        }
        if (!empty($this->cssFiles)) {
            $lines[] = implode("\n", $this->cssFiles);
        }
        if (!empty($this->css)) {
            $lines[] = implode("\n", $this->css);
        }
        if (!empty($this->jsFiles[self::POS_HEAD])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_HEAD]);
        }
        if (!empty($this->js[self::POS_HEAD])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_HEAD]), ['type' => 'text/javascript']);
        }

        return empty($lines) ? "" : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the end of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @param boolean $ajaxMode whether the view is rendering in AJAX mode.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     * @return string the rendered content
     */
    protected function renderBodyEndHtml($ajaxMode) {
        if (!empty($this->jsFiles[self::POS_END])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_END]);
        }

        if ($ajaxMode) {
            $scripts = [];
            if (!empty($this->js[self::POS_END])) {
                $scripts[] = implode("\n", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $scripts[] = implode("\n", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $scripts[] = implode("\n", $this->js[self::POS_LOAD]);
            }
        {
                $lines[] = Html::script(implode("\n", $scripts), ['type' => 'text/javascript']);
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = Html::script(implode("\n", $this->js[self::POS_END]), ['type' => 'text/javascript']);
            }
            if (!empty($this->js[self::POS_READY])) {
                $js = "jQuery(document).ready(function () {\n" . implode("\n", $this->js[self::POS_READY]) . "\n});";
                $lines[] = Html::script($js, ['type' => 'text/javascript']);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $js = "jQuery(window).load(function () {\n" . implode("\n", $this->js[self::POS_LOAD]) . "\n});";
                $lines[] = Html::script($js, ['type' => 'text/javascript']);
            }
        }

        return (empty($lines) ?  "": implode("\n", $lines)) . "\n";
    }

    /**
     * Renders the content to be inserted at the beginning of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderBodyBeginHtml() {
        $lines = [];
        if (!empty($this->jsFiles[self::POS_BEGIN])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_BEGIN]);
        }
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_BEGIN]), ['type' => 'text/javascript']);
        }

        return (empty($lines) ? "" : implode("\n", $lines)) . "\n";
    }

}
