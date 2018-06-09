<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

use Kant\Foundation\Event;

/**
 * ViewEvent represents events triggered by the [[View]] component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewEvent extends Event
{

    /**
     *
     * @var string the view file being rendered.
     */
    public $viewFile;

    /**
     *
     * @var array the parameter array passed to the [[View::render()]] method.
     */
    public $params;

    /**
     *
     * @var string the rendering result of [[View::renderFile()]].
     *      Event handlers may modify this property and the modified output will be
     *      returned by [[View::renderFile()]]. This property is only used
     *      by [[View::EVENT_AFTER_RENDER]] event.
     */
    public $output;

    /**
     *
     * @var boolean whether to continue rendering the view file. Event handlers of
     *      [[View::EVENT_BEFORE_RENDER]] may set this property to decide whether
     *      to continue rendering the current view file.
     */
    public $isValid = true;
}
