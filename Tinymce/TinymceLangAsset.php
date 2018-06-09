<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Tinymce;

use Kant\Kant;
use Kant\View\AssetBundle;

class TinymceLangAsset extends AssetBundle
{

    public $js = [];

    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Assets\\langs\\';
        $jsFileName = Kant::$app->language . ".js";
        if (file_exists($this->sourcePath . $jsFileName)) {
            $this->js = [
                Kant::$app->language . ".js"
            ];
        }
    }
}
