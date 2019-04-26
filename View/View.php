<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

use Kant\Kant;
use Kant\Helper\Html;
use Kant\Helper\ArrayHelper;
use Kant\Helper\Inflector;
use Kant\Support\Arr;
use Kant\Support\ViewErrorBag;
use Kant\Widget\FragmentCache;
use Kant\Exception\InvalidCallException;

/**
 * View class
 * 
 * @access public
 * @version 1.1
 * @since version 1.0
 */
class View extends BaseView
{

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
     *
     * @var AssetBundle[] list of the registered asset bundles. The keys are the bundle names, and the values
     *      are the registered [[AssetBundle]] objects.
     * @see registerAssetBundle()
     */
    public $assetBundles = [];

    /**
     *
     * @var array the registered meta tags.
     * @see registerMetaTag()
     */
    public $metaTags;

    /**
     *
     * @var array the registered link tags.
     * @see registerLinkTag()
     */
    public $linkTags;

    /**
     *
     * @var array the registered CSS code blocks.
     * @see registerCss()
     */
    public $css;

    /**
     *
     * @var array the registered CSS files.
     * @see registerCssFile()
     */
    public $cssFiles;

    /**
     *
     * @var array the registered JS code blocks
     * @see registerJs()
     */
    public $js;

    /**
     *
     * @var array the registered JS files.
     * @see registerJsFile()
     */
    public $jsFiles;

    /**
     * dispatcher
     *
     * @var string
     */
    public $dispatcher;

    /**
     *
     * @var null|string|false the name of the layout to be applied to this controller's views.
     *      This property mainly affects the behavior of [[render()]].
     *      If false, no layout will be applied.
     */
    public $layout = 'main';

    /**
     *
     * @var string the root directory that contains layout view files for this module.
     */
    private $_layoutPath;

    private $_assetManager;

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     *
     * @var array a list of currently active fragment cache widgets. This property
     *      is used internally to implement the content caching feature. Do not modify it directly.
     * @internal
     *
     */
    public $cacheStack = [];


    /**
     * Add a piece of shared data to the environment.
     *
     * @param array|string $key            
     * @param mixed $value            
     * @return mixed
     */
    public function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [
            $key => $value
        ];
        
        foreach ($keys as $key => $value) {
            $this->shared[$key] = $value;
        }
        
        return $value;
    }

    /**
     * Get an item from the shared data.
     *
     * @param string $key            
     * @param mixed $default            
     * @return mixed
     */
    public function shared($key, $default = null)
    {
        return Arr::get($this->shared, $key, $default);
    }

    /**
     * Putting the errors in the view for every view allows the developer to just
     * assume that some errors are always available, which is convenient since
     * they don't have to continually run checks for the presence of errors.
     */
    public function ShareErrorsFromSession()
    {
        // If the current session has an "errors" variable bound to it, we will share
        // its value with all view instances so the views can easily access errors
        // without having to bind. An empty bag is set when there aren't errors.
        $this->share('errors', Kant::$app->getSession()
            ->get('errors') ?  : new ViewErrorBag());
    }

    /**
     * Marks the position of an HTML head section.
     */
    public function head()
    {
        echo self::PH_HEAD;
    }

    /**
     * Marks the beginning of an HTML body section.
     */
    public function beginBody()
    {
        echo self::PH_BODY_BEGIN;
        $this->trigger(self::EVENT_BEGIN_BODY);
    }

    /**
     * Marks the ending of an HTML body section.
     */
    public function endBody()
    {
        $this->trigger(self::EVENT_END_BODY);
        echo self::PH_BODY_END;
        foreach (array_keys($this->assetBundles) as $bundle) {
            $this->registerAssetFiles($bundle);
        }
    }

    /**
     * Marks the ending of an HTML page.
     * 
     * @param boolean $ajaxMode
     *            whether the view is rendering in AJAX mode.
     *            If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     *            will be rendered at the end of the view like normal scripts.
     */
    public function endPage($ajaxMode = false)
    {
        $this->trigger(self::EVENT_END_PAGE);
        
        $content = ob_get_clean();
        
        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode)
        ]);
        
        $this->clear();
    }

    /**
     * Returns the value of a property.
     *
     * @param type $key            
     * @return type
     */
    public function __get($key)
    {
        if (isset($this->params[$key])) {
            return ($this->params[$key]);
        } else {
            return (NULL);
        }
    }

    /**
     * Sets the value of a property.
     *
     * @param string $key            
     * @param mixed $value            
     */
    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Fetach template
     *
     * @param type $view            
     * @return type
     * @throws RuntimeException
     */
    public function fetch($view = '', $params = [])
    {
        $viewFile = $this->findViewFile($view);
        return $this->renderFile($viewFile, $params);
    }

    /**
     * Renders a view and applies layout if available.
     *
     * @param type $view            
     */

    public function render($view = "", $params = [], $context = null)
    {		
        $content = $this->fetch($view, $params);
        return $this->renderContent($content);
    }
	
	/**
     * Renders a static string by applying a layout.
     * @param string $content the static string being rendered
     * @return string the rendering result of the layout with the given static string as the `$content` variable.
     * If the layout is disabled, the string will be returned back.
     * @since 2.0.1
     */
    public function renderContent($content)
    {
        $layoutFile = $this->findLayoutFile();
        if ($layoutFile !== false) {
            return $this->renderFile($layoutFile, [
                'content' => $content
            ]);
        } 
		return $content;
    }
	
	/**
     * Renders a view without applying layout.
     * This method differs from [[render()]] in that it does not apply any layout.
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     * @throws InvalidParamException if the view file does not exist.
     */
    public function renderPartial($view, $params = [])
    {
        return $this->render($view, $params, $this);
    }

    /**
     * Finds the view file based on the given view name.
     *
     * @param string $view            
     */

    public function findViewFile($view = '', $context = null)
    {
        if (strncmp($view, '@', 1) === 0) {
            // e.g. "@app/views/main"
            $file = Kant::getAlias($view);
        } elseif (strncmp($view, '//', 2) === 0) {
            // e.g. "//layouts/main"
            $file = Kant::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
        } elseif (strncmp($view, '/', 1) === 0) {
            // e.g. "/site/index"
            if (Kant::$app->controller !== null) {
                $file = Kant::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
            } else {
                try {
                    $file = $this->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
                } catch(\Exception $e) {
                    throw new InvalidCallException("Unable to locate view file for view '$view': no active controller.");
                }
            }
        } elseif ($context instanceof ViewContextInterface) {
            $file = $context->getViewPath() . DIRECTORY_SEPARATOR . $view;
        } elseif (($currentViewFile = $this->getViewFile()) !== false) {
            $file = dirname($currentViewFile) . DIRECTORY_SEPARATOR . $view;
        } else {
            try {
                if (!empty(Kant::$app->controller->module)) {
					$_controllerId = str_replace('/', DIRECTORY_SEPARATOR, Inflector::camel2id(Kant::$app->controller->id));
                    $file = Kant::$app->controller->module->getViewPath()  . DIRECTORY_SEPARATOR  . $_controllerId . DIRECTORY_SEPARATOR .  ltrim($view, '/') ;
                } else {
                    $file = $this->getViewPath()  . DIRECTORY_SEPARATOR . ltrim($view, '/');
                }

            } catch(\Exception $e) {
                throw new InvalidCallException("Unable to resolve view file for view '$view': no active view context.");
            }
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.' . $this->defaultExtension;
        if ($this->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }


    /**
     * Finds the applicable layout file.
     *
     * @param type $view            
     * @return boolean|string
     */

    public function findLayoutFile()
    {
        if ($this->layout === false) {
            return false;
        }

        if (is_string($this->layout)) {
            $layout = $this->layout;
        }

        if (strncmp($layout, '@', 1) === 0) {
            $file = Kant::getAlias($layout);
        } elseif (strncmp($layout, '/', 1) === 0) {
            $file = $this->getLayoutPath() . DIRECTORY_SEPARATOR . substr($layout, 1);
        } else {
            $file = $this->getLayoutPath() . DIRECTORY_SEPARATOR . $layout;
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.' . $this->defaultExtension;
        if ($this->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }

    /**
     * Get view path
     */
    public function getViewPath()
    {
        if (!empty($this->dispatcher)) {
            $dispatcherArr = explode('/', trim($this->dispatcher, '/'));
            $module = $dispatcherArr[0];
            $viewPath = Kant::getAlias('@tpl_path') . DIRECTORY_SEPARATOR  . $module;
        } else {
            $viewPath = Kant::getAlias('@tpl_path');
        }
        return $viewPath;
    }


    /**
     * Returns the directory that contains layout view files for this module.
     *
     * @return string
     */

    public function getLayoutPath()
    {
        if ($this->_layoutPath === null) {
            $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR  . 'layouts';
        }
        return $this->_layoutPath;
    }



    /**
     * Renders a view in response to an AJAX request.
     *
     * This method is similar to [[render()]] except that it will surround the view being rendered
     * with the calls of [[beginPage()]], [[head()]], [[beginBody()]], [[endBody()]] and [[endPage()]].
     * By doing so, the method is able to inject into the rendering result with JS/CSS scripts and files
     * that are registered with the view.
     *
     * @param string $view the view name. Please refer to [[render()]] on how to specify this parameter.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @param object $context the context that the view should use for rendering the view. If null,
     * existing [[context]] will be used.
     * @return string the rendering result
     * @see render()
     */
    public function renderAjax($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);

        ob_start();
        ob_implicit_flush(false);

        $this->beginPage();
        $this->head();
        $this->beginBody();
        echo $this->renderFile($viewFile, $params, $context);
        $this->endBody();
        $this->endPage(true);

        return ob_get_clean();
    }

    /**
     * Registers the asset manager being used by this view object.
     * 
     * @return \Kant\View\AssetManager the asset manager. Defaults to the "assetManager" application component.
     */
    public function getAssetManager()
    {
        return $this->_assetManager ?  : Kant::$app->getAssetManager();
    }

    /**
     * Sets the asset manager.
     * @param \Kant\View\AssetManager $value the asset manager
     */
    public function setAssetManager($value)
    {
        $this->_assetManager = $value;
    }


    /**
     * Clears up the registered meta tags, link tags, css/js scripts and files.
     */
    public function clear()
    {
        $this->metaTags = null;
        $this->linkTags = null;
        $this->css = null;
        $this->cssFiles = null;
        $this->js = null;
        $this->jsFiles = null;
        $this->assetBundles = [];
    }

    /**
     * Registers all files provided by an asset bundle including depending bundles files.
     * Removes a bundle from [[assetBundles]] once files are registered.
     *
     * @param string $name
     *            name of the bundle to register
     */
    protected function registerAssetFiles($name)
    {
        if (!isset($this->assetBundles[$name])) {
            return;
        }
        $bundle = $this->assetBundles[$name];
        if ($bundle) {
            foreach ($bundle->depends as $dep) {
                $this->registerAssetFiles($dep);
            }
            $bundle->registerAssetFiles($this);
        }
        unset($this->assetBundles[$name]);
    }

    /**
     * Registers the named asset bundle.
     * All dependent asset bundles will be registered.
     * 
     * @param string $name
     *            the class name of the asset bundle (without the leading backslash)
     * @param integer|null $position
     *            if set, this forces a minimum position for javascript files.
     *            This will adjust depending assets javascript file position or fail if requirement can not be met.
     *            If this is null, asset bundles position settings will not be changed.
     *            See [[registerJsFile]] for more details on javascript position.
     * @return AssetBundle the registered asset bundle instance
     * @throws InvalidConfigException if the asset bundle does not exist or a circular dependency is detected
     */
    public function registerAssetBundle($name, $position = null)
    {
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
     * Registers a meta tag.
     *
     * For example, a description meta tag can be added like the following:
     *
     * ```php
     * $view->registerMetaTag([
     * 'name' => 'description',
     * 'content' => 'This website is about funny raccoons.'
     * ]);
     * ```
     *
     * will result in the meta tag `<meta name="description" content="This website is about funny raccoons.">`.
     *
     * @param array $options
     *            the HTML attributes for the meta tag.
     * @param string $key
     *            the key that identifies the meta tag. If two meta tags are registered
     *            with the same key, the latter will overwrite the former. If this is null, the new meta tag
     *            will be appended to the existing ones.
     */
    public function registerMetaTag($options, $key = null)
    {
        if ($key === null) {
            $this->metaTags[] = Html::tag('meta', '', $options);
        } else {
            $this->metaTags[$key] = Html::tag('meta', '', $options);
        }
    }

    /**
     * Registers a link tag.
     *
     * For example, a link tag for a custom [favicon](http://www.w3.org/2005/10/howto-favicon)
     * can be added like the following:
     *
     * ```php
     * $view->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => '/myicon.png']);
     * ```
     *
     * which will result in the following HTML: `<link rel="icon" type="image/png" href="/myicon.png">`.
     *
     * **Note:** To register link tags for CSS stylesheets, use [[registerCssFile()]] instead, which
     * has more options for this kind of link tag.
     *
     * @param array $options
     *            the HTML attributes for the link tag.
     * @param string $key
     *            the key that identifies the link tag. If two link tags are registered
     *            with the same key, the latter will overwrite the former. If this is null, the new link tag
     *            will be appended to the existing ones.
     */
    public function registerLinkTag($options, $key = null)
    {
        if ($key === null) {
            $this->linkTags[] = Html::tag('link', '', $options);
        } else {
            $this->linkTags[$key] = Html::tag('link', '', $options);
        }
    }

    /**
     * Registers a CSS code block.
     * 
     * @param string $css
     *            the content of the CSS code block to be registered
     * @param array $options
     *            the HTML attributes for the `<style>`-tag.
     * @param string $key
     *            the key that identifies the CSS code block. If null, it will use
     *            $css as the key. If two CSS code blocks are registered with the same key, the latter
     *            will overwrite the former.
     */
    public function registerCss($css, $options = [], $key = null)
    {
        $key = $key ?  : md5($css);
        $this->css[$key] = Html::style($css, $options);
    }

    /**
     * Registers a CSS file.
     * 
     * @param string $url
     *            the CSS file to be registered.
     * @param array $options
     *            the HTML attributes for the link tag. Please refer to [[Html::cssFile()]] for
     *            the supported options. The following options are specially handled and are not treated as HTML attributes:
     *            
     *            - `depends`: array, specifies the names of the asset bundles that this CSS file depends on.
     *            
     * @param string $key
     *            the key that identifies the CSS script file. If null, it will use
     *            $url as the key. If two CSS files are registered with the same key, the latter
     *            will overwrite the former.
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $url = Kant::getAlias($url);
        $key = $key ?  : $url;
        $depends = ArrayHelper::remove($options, 'depends', []);
        
        if (empty($depends)) {
            $this->cssFiles[$key] = Html::cssFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = new AssetBundle([
                'baseUrl' => '',
                'css' => [
                    strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')
                ],
                'cssOptions' => $options,
                'depends' => (array) $depends
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Registers a JS code block.
     * 
     * @param string $js
     *            the JS code block to be registered
     * @param integer $position
     *            the position at which the JS script tag should be inserted
     *            in a page. The possible values are:
     *            
     *            - [[POS_HEAD]]: in the head section
     *            - [[POS_BEGIN]]: at the beginning of the body section
     *            - [[POS_END]]: at the end of the body section
     *            - [[POS_LOAD]]: enclosed within jQuery(window).load().
     *            Note that by using this position, the method will automatically register the jQuery js file.
     *            - [[POS_READY]]: enclosed within jQuery(document).ready(). This is the default value.
     *            Note that by using this position, the method will automatically register the jQuery js file.
     *            
     * @param string $key
     *            the key that identifies the JS code block. If null, it will use
     *            $js as the key. If two JS code blocks are registered with the same key, the latter
     *            will overwrite the former.
     */
    public function registerJs($js, $position = self::POS_READY, $key = null)
    {
        $key = $key ?  : md5($js);
        $this->js[$position][$key] = $js;
        if ($position === self::POS_READY || $position === self::POS_LOAD) {
            JqueryAsset::register($this);
        }
    }

    /**
     * Registers a JS file.
     * 
     * @param string $url
     *            the JS file to be registered.
     * @param array $options
     *            the HTML attributes for the script tag. The following options are specially handled
     *            and are not treated as HTML attributes:
     *            
     *            - `depends`: array, specifies the names of the asset bundles that this JS file depends on.
     *            - `position`: specifies where the JS script tag should be inserted in a page. The possible values are:
     *            * [[POS_HEAD]]: in the head section
     *            * [[POS_BEGIN]]: at the beginning of the body section
     *            * [[POS_END]]: at the end of the body section. This is the default value.
     *            
     *            Please refer to [[Html::jsFile()]] for other supported options.
     *            
     * @param string $key
     *            the key that identifies the JS script file. If null, it will use
     *            $url as the key. If two JS files are registered with the same key, the latter
     *            will overwrite the former.
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $url = Kant::getAlias($url);
        $key = $key ?  : $url;
        $depends = ArrayHelper::remove($options, 'depends', []);
        
        if (empty($depends)) {
            $position = ArrayHelper::remove($options, 'position', self::POS_END);
            $this->jsFiles[$position][$key] = Html::jsFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = new AssetBundle([
                'baseUrl' => '',
                'js' => [
                    strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')
                ],
                'jsOptions' => $options,
                'depends' => (array) $depends
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Renders the content to be inserted in the head section.
     * The content is rendered using the registered meta tags, link tags, CSS/JS code blocks and files.
     * 
     * @return string the rendered content
     */
    protected function renderHeadHtml()
    {
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
            $lines[] = Html::script(implode("\n", $this->js[self::POS_HEAD]), [
                'type' => 'text/javascript'
            ]);
        }
        
        return (empty($lines) ? "" : implode("\n", $lines)) . "\n";
    }

    /**
     * Renders the content to be inserted at the end of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * 
     * @param boolean $ajaxMode
     *            whether the view is rendering in AJAX mode.
     *            If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     *            will be rendered at the end of the view like normal scripts.
     * @return string the rendered content
     */
    protected function renderBodyEndHtml($ajaxMode)
    {
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
                $lines[] = Html::script(implode("\n", $scripts), [
                    'type' => 'text/javascript'
                ]);
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = Html::script(implode("\n", $this->js[self::POS_END]), [
                    'type' => 'text/javascript'
                ]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $js = "jQuery(document).ready(function () {\n" . implode("\n", $this->js[self::POS_READY]) . "\n});";
                $lines[] = Html::script($js, [
                    'type' => 'text/javascript'
                ]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $js = "jQuery(window).load(function () {\n" . implode("\n", $this->js[self::POS_LOAD]) . "\n});";
                $lines[] = Html::script($js, [
                    'type' => 'text/javascript'
                ]);
            }
        }
        
        return (empty($lines) ? "" : implode("\n", $lines)) . "\n";
    }

    /**
     * Renders the content to be inserted at the beginning of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * 
     * @return string the rendered content
     */
    protected function renderBodyBeginHtml()
    {
        $lines = [];
        if (!empty($this->jsFiles[self::POS_BEGIN])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_BEGIN]);
        }
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_BEGIN]), [
                'type' => 'text/javascript'
            ]);
        }
        
        return (empty($lines) ? "" : implode("\n", $lines)) . "\n";
    }
}
