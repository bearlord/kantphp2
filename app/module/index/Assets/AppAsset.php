<?php

namespace app\module\index\Assets;

use Kant\View\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'Kant\View\KantAsset',
        'Kant\Bootstrap\BootstrapAsset',
        'Kant\Bootstrap\BootstrapPluginAsset',
    ];
}
