<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\View;

/**
 * This asset bundle provides the base javascript files for the KantPHP Framework.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class KantAsset extends AssetBundle
{

    public $sourcePath = '@kant/assets';

    public $js = [
        'kant.js'
    ];

    public $depends = [
        'Kant\View\JqueryAsset'
    ];
}
