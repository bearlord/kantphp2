<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kant\Widget;

use Kant\View\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFormAsset extends AssetBundle
{
    public $sourcePath = '@yii/assets';
    public $js = [
        'yii.activeForm.js',
    ];
    public $depends = [
        'Kant\View\KantAsset',
    ];
}
