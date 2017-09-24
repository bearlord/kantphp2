<?php
namespace Kant\View;

/**
 * Main frontend application asset bundle.
 */
class ExceptionAsset extends AssetBundle
{

    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $css = [
        'css/site.css'
    ];

    public $js = [];

    public $depends = [
        'Kant\View\KantAsset',
        'Kant\Bootstrap\BootstrapAsset'
    ];
}
