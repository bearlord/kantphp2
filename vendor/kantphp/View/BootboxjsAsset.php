<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

/**
 * This asset bundle provides the [bootbox.js library](http://bootboxjs.com/)
 *
 * @author Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @since 2.0
 */
class BootboxjsAsset extends AssetBundle
{

    public $sourcePath = '@bower/bootbox.js';

    public $js = [
        'bootbox.js'
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}
