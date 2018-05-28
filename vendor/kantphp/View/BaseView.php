<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

use Kant\Foundation\Component;

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
