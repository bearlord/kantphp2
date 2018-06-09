<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Widget;

use Kant\Widget\Widget;

/**
 * Block records all output between [[begin()]] and [[end()]] calls and stores it in [[\Kant\View\View::$blocks]].
 * for later use.
 *
 * [[\Kant\View\View]] component contains two methods [[\Kant\Kant\View::beginBlock()]] and [[\Kant\Kant\View::endBlock()]].
 * The general idea is that you're defining block default in a view or layout:
 *
 * ```php
 * <?php $this->beginBlock('messages', true) ?>
 * Nothing.
 * <?php $this->endBlock() ?>
 * ```
 *
 * And then overriding default in sub-views:
 *
 * ```php
 * <?php $this->beginBlock('username') ?>
 * Umm... hello?
 * <?php $this->endBlock() ?>
 * ```
 *
 * Second parameter defines if block content should be outputted which is desired when rendering its content but isn't
 * desired when redefining it in subviews.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Block extends Widget
{

    /**
     * @var bool whether to render the block content in place. Defaults to false,
     * meaning the captured block content will not be displayed.
     */
    public $renderInPlace = false;

    /**
     * Starts recording a block.
     */
    public function init()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * Ends recording a block.
     * This method stops output buffering and saves the rendering result as a named block in the view.
     */
    public function run()
    {
        $block = ob_get_clean();
        if ($this->renderInPlace) {
            echo $block;
        }
        $this->view->blocks[$this->getId()] = $block;
    }

}
