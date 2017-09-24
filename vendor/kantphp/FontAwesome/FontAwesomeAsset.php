<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\FontAwesome;

use Kant\View\AssetBundle;

/**
 * This asset bundle provides the [Font Awesome library](http://fontawesome.io/)
 *
 * @author Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @since 2.0
 */
class FontAwesomeAsset extends AssetBundle
{

    public $sourcePath = '@bower/font-awesome';

    public $css = [
        'css/font-awesome.min.css'
    ];
}
