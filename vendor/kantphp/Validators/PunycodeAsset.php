<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Validators;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[EmailValidator]]s client validation.
 *
 */
class PunycodeAsset extends AssetBundle {

    public $sourcePath = '@bower/punycode';
    public $js = [
        'punycode.js',
    ];

}
