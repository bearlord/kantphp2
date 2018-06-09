<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kant\Widget;

use Kant\View\AssetBundle;

/**
 * This asset bundle provides the javascript files required by [[Pjax]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PjaxAsset extends AssetBundle
{
    public $sourcePath = '@bower/kant-pjax';
    public $js = [
        'jquery.pjax.js',
    ];
    public $depends = [
        'Kant\View\KantAsset',
    ];
}
