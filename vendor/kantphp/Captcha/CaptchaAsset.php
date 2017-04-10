<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kant\Captcha;

use Kant\View\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[Captcha]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaAsset extends AssetBundle
{
    public $sourcePath = '@kant/assets';
    public $js = [
        'kant.captcha.js',
    ];
    public $depends = [
        'Kant\View\KantAsset',
    ];
}
