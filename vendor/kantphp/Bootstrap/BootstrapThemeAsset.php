<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Bootstrap;

use Kant\View\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap default theme.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BootstrapThemeAsset extends AssetBundle
{

    public $sourcePath = '@bower/bootstrap/dist';

    public $css = [
        'css/bootstrap-theme.css'
    ];

    public $depends = [
        'Kant\Bootstrap\BootstrapAsset'
    ];
}
