<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Helper\FileHelper;
use Kant\Exception\InvalidCallException;
use Kant\Exception\ViewNotFoundException;

class BaseView extends Component
{

    /**
     * @event Event an event that is triggered by [[beginPage()]].
     */
    const EVENT_BEGIN_PAGE = 'beginPage';

    /**
     * @event Event an event that is triggered by [[endPage()]].
     */
    const EVENT_END_PAGE = 'endPage';

    /**
     * @event ViewEvent an event that is triggered by [[renderFile()]] right before it renders a view file.
     */
    const EVENT_BEFORE_RENDER = 'beforeRender';

    /**
     * @event ViewEvent an event that is triggered by [[renderFile()]] right after it renders a view file.
     */
    const EVENT_AFTER_RENDER = 'afterRender';

    /**
     * @var ViewContextInterface the context under which the [[renderFile()]] method is being invoked.
     */
    public $context;
    /**
     * @var mixed custom parameters that are shared among view templates.
     */
    public $params = [];
    /**
     * @var array a list of available renderers indexed by their corresponding supported file extensions.
     * Each renderer may be a view renderer object or the configuration for creating the renderer object.
     * For example, the following configuration enables both Smarty and Twig view renderers:
     *
     * ```php
     * [
     *     'tpl' => ['class' => 'yii\smarty\ViewRenderer'],
     *     'twig' => ['class' => 'yii\twig\ViewRenderer'],
     * ]
     * ```
     *
     * If no renderer is available for the given view file, the view file will be treated as a normal PHP
     * and rendered via [[renderPhpFile()]].
     */
    public $renderers;
    /**
     * @var string the default view file extension. This will be appended to view file names if they don't have file extensions.
     */
    public $defaultExtension = 'php';
    /**
     * @var Theme|array|string the theme object or the configuration for creating the theme object.
     * If not set, it means theming is not enabled.
     */
    public $theme;
    /**
     * @var array a list of named output blocks. The keys are the block names and the values
     * are the corresponding block content. You can call [[beginBlock()]] and [[endBlock()]]
     * to capture small fragments of a view. They can be later accessed somewhere else
     * through this property.
     */
    public $blocks;
    /**
     * @var array a list of currently active fragment cache widgets. This property
     * is used internally to implement the content caching feature. Do not modify it directly.
     * @internal
     */
    public $cacheStack = [];
    /**
     * @var array a list of placeholders for embedding dynamic contents. This property
     * is used internally to implement the content caching feature. Do not modify it directly.
     * @internal
     */
    public $dynamicPlaceholders = [];

    /**
     * @var array the view files currently being rendered. There may be multiple view files being
     * rendered at a moment because one view may be rendered within another.
     */
    private $_viewFiles = [];

    /**
     * Initializes the view component.
     */
    public function init()
    {
        var_dump($this->theme);
        parent::init();
        if (is_array($this->theme)) {
            if (!isset($this->theme['class'])) {
                $this->theme['class'] = '\Kant\Foundation\Theme';
            }
            $this->theme = Kant::createObject($this->theme);
        } elseif (is_string($this->theme)) {
            $this->theme = Kant::createObject($this->theme);
        }
    }

    /**
     * Renders a view.
     *
     * The view to be rendered can be specified in one of the following formats:
     *
     * - [path alias](guide:concept-aliases) (e.g. "@app/views/site/index");
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     * - absolute path within current module (e.g. "/site/index"): the view name starts with a single slash.
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the [[Controller::module|current module]].
     * - relative view (e.g. "index"): the view name does not start with `@` or `/`. The corresponding view file will be
     *   looked for under the [[ViewContextInterface::getViewPath()|view path]] of the view `$context`.
     *   If `$context` is not given, it will be looked for under the directory containing the view currently
     *   being rendered (i.e., this happens when rendering a view within another view).
     *
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @param object $context the context to be assigned to the view and can later be accessed via [[context]]
     * in the view. If the context implements [[ViewContextInterface]], it may also be used to locate
     * the view file corresponding to a relative view name.
     * @return string the rendering result
     * @throws ViewNotFoundException if the view file does not exist.
     * @throws InvalidCallException if the view cannot be resolved.
     * @see renderFile()
     */
    public function render($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);
        return $this->renderFile($viewFile, $params, $context);
    }

    /**
     * Finds the view file based on the given view name.
     * @param string $view the view name or the [path alias](guide:concept-aliases) of the view file. Please refer to [[render()]]
     * on how to specify this parameter.
     * @param object $context the context to be assigned to the view and can later be accessed via [[context]]
     * in the view. If the context implements [[ViewContextInterface]], it may also be used to locate
     * the view file corresponding to a relative view name.
     * @return string the view file path. Note that the file may not exist.
     * @throws InvalidCallException if a relative view name is given while there is no active context to
     * determine the corresponding view file.
     */
    protected function findViewFile($view, $context = null)
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
                throw new InvalidCallException("Unable to locate view file for view '$view': no active controller.");
            }
        } elseif ($context instanceof ViewContextInterface) {
            $file = $context->getViewPath() . DIRECTORY_SEPARATOR . $view;
        } elseif (($currentViewFile = $this->getViewFile()) !== false) {
            $file = dirname($currentViewFile) . DIRECTORY_SEPARATOR . $view;
        } else {
            throw new InvalidCallException("Unable to resolve view file for view '$view': no active view context.");
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
     * Renders a view file.
     *
     * If [[theme]] is enabled (not null), it will try to render the themed version of the view file as long
     * as it is available.
     *
     * The method will call [[FileHelper::localize()]] to localize the view file.
     *
     * If [[renderers|renderer]] is enabled (not null), the method will use it to render the view file.
     * Otherwise, it will simply include the view file as a normal PHP file, capture its output and
     * return it as a string.
     *
     * @param string $viewFile the view file. This can be either an absolute file path or an alias of it.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @param object $context the context that the view should use for rendering the view. If null,
     * existing [[context]] will be used.
     * @return string the rendering result
     * @throws ViewNotFoundException if the view file does not exist
     */
    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = Kant::getAlias($viewFile);

        if ($this->theme !== null) {
            $viewFile = $this->theme->applyTo($viewFile);
        }
        if (is_file($viewFile)) {
            $viewFile = FileHelper::localize($viewFile);
        } else {
            throw new ViewNotFoundException("The view file does not exist: $viewFile");
        }

        $oldContext = $this->context;
        if ($context !== null) {
            $this->context = $context;
        }
        $output = '';
        $this->_viewFiles[] = $viewFile;

        if ($this->beforeRender($viewFile, $params)) {
            Kant::trace("Rendering view file: $viewFile", __METHOD__);
            $ext = pathinfo($viewFile, PATHINFO_EXTENSION);
            if (isset($this->renderers[$ext])) {
                if (is_array($this->renderers[$ext]) || is_string($this->renderers[$ext])) {
                    $this->renderers[$ext] = Kant::createObject($this->renderers[$ext]);
                }
                /* @var $renderer ViewRenderer */
                $renderer = $this->renderers[$ext];
                $output = $renderer->render($this, $viewFile, $params);
            } else {
                $output = $this->renderPhpFile($viewFile, $params);
            }
            $this->afterRender($viewFile, $params, $output);
        }

        array_pop($this->_viewFiles);
        $this->context = $oldContext;

        return $output;
    }


    /**
     * @return string|bool the view file currently being rendered. False if no view file is being rendered.
     */
    public function getViewFile()
    {
        return end($this->_viewFiles);
    }

    /**
     * This method is invoked right before [[renderFile()]] renders a view file.
     * The default implementation will trigger the [[EVENT_BEFORE_RENDER]] event.
     * If you override this method, make sure you call the parent implementation first.
     * 
     * @param string $viewFile
     *            the view file to be rendered.
     * @param array $params
     *            the parameter array passed to the [[render()]] method.
     * @return boolean whether to continue rendering the view file.
     */
    public function beforeRender($viewFile, $params)
    {
        $event = new ViewEvent([
            'viewFile' => $viewFile,
            'params' => $params
        ]);
        $this->trigger(self::EVENT_BEFORE_RENDER, $event);
        
        return $event->isValid;
    }

    /**
     * This method is invoked right after [[renderFile()]] renders a view file.
     * The default implementation will trigger the [[EVENT_AFTER_RENDER]] event.
     * If you override this method, make sure you call the parent implementation first.
     * @param string $viewFile the view file being rendered.
     * @param array $params the parameter array passed to the [[render()]] method.
     * @param string $output the rendering result of the view file. Updates to this parameter
     * will be passed back and returned by [[renderFile()]].
     */
    public function afterRender($viewFile, $params, &$output)
    {
        if ($this->hasEventHandlers(self::EVENT_AFTER_RENDER)) {
            $event = new ViewEvent([
                'viewFile' => $viewFile,
                'params' => $params,
                'output' => $output,
            ]);
            $this->trigger(self::EVENT_AFTER_RENDER, $event);
            $output = $event->output;
        }
    }

    /**
     * Marks the beginning of a page.
     */
    public function beginPage()
    {
        ob_start();
        ob_implicit_flush(false);
        
        $this->trigger(self::EVENT_BEGIN_PAGE);
    }

    /**
     * Marks the ending of a page.
     */
    public function endPage()
    {
        $this->trigger(self::EVENT_END_PAGE);
        ob_end_flush();
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
}
