<?php
/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Filemanager;


use Kant\View\AssetBundle;
use Kant\View\View;

class FilemanagerAsset extends AssetBundle
{
//    public $basePath = '@webroot';
//
//    public $baseUrl = '@web';

    public $css = [
        'css/style.css',
        'dropzone/kant.css',
        'contexmenu/jquery.contextMenu.css',
        'contexmenu/kant.css'
    ];
    public $js = [
        'dropzone/dropzone.min.js',
        'contexmenu/jquery.contextMenu.js',
        'bootbox/bootbox.min.js',
        'js/filemanager.js'
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

    public $depends = [
        'Kant\View\KantAsset',
        'Kant\Bootstrap\BootstrapAsset',
        'Kant\Bootstrap\BootstrapPluginAsset',
        'Kant\FontAwesome\FontAwesomeAsset',
        'Kant\View\BootboxjsAsset',
        'Kant\View\JqueryLazyloadAsset',
    ];


    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Assets';
    }
}