<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

/**
 * This asset bundle provides the [jquery lazyload library](https://appelsiini.net/projects/lazyload/)
 *
 * @author Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @since 2.0
 */
class JqueryLazyloadAsset extends AssetBundle
{

    public $sourcePath = '@bower/jquery_lazyload';

    public $js = [
        'jquery.lazyload.js'
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}
