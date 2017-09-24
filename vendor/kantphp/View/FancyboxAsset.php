<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

/**
 * This asset bundle provides the [Fancybox javascript library](http://fancyapps.com/fancybox/)
 *
 * @author Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @since 2.0
 */
class FancyboxAsset extends AssetBundle
{

    public $sourcePath = '@bower/fancybox/dist';


    public $css = [
        'jquery.fancybox.css'
    ];

    public $js = [
        'jquery.fancybox.min.js'
    ];


    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

    public $depends = [
        'Kant\View\JqueryAsset'
    ];
}
