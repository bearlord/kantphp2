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
