<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Grid;

use Kant\View\AssetBundle;

/**
 * This asset bundle provides the javascript files for the [[GridView]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridViewAsset extends AssetBundle
{

    public $sourcePath = '@kant/assets';

    public $js = [
        'kant.gridView.js'
    ];

    public $depends = [
        'Kant\View\KantAsset'
    ];
}
