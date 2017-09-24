<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Bootstrap;

/**
 * \Kant\Bootstrap\Widget is the base class for all bootstrap widgets.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Widget extends \Kant\Widget\Widget
{
    
    use BootstrapWidgetTrait;

    /**
     *
     * @var array the HTML attributes for the widget container tag.
     * @see \Kant\Helper\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
}
