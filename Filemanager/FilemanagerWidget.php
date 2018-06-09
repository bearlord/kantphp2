<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/13 0013
 * Time: 22:04
 */

namespace Kant\Filemanager;


use Kant\Helper\Html;
use Kant\Helper\JsExpression;
use Kant\Kant;
use Kant\View\FancyboxAsset;
use Kant\Widget\InputWidget;

class FilemanagerWidget extends InputWidget
{
    /**
     *
     * @var array the HTML attributes for the input tag.
     * @see \Kant\Helper\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [
        'class' => 'form-control'
    ];

    public $template = [
        '{input}<span class="input-group-btn"><a class="btn btn-default btn-iframe" href="{url}">{select}</a></span>',
        '{input}<span class="input-group-btn"><button class="btn btn-default btn-popup-filemanager" data-url="{url}" type="button">{select}</button></span>',
    ];

    public $clientOptions = [];

    protected $fullUrl;

    protected $popup = 0;

    public function init()
    {
        parent::init();
        $this->id = $this->options['id'];
    }

    public function run()
    {
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            $input = Html::input($this->id, $this->value, [
                'id' => $this->id
            ]);
        }

        $this->fullUrl = $this->buildFullUrl();
        $this->popup = $this->getPopupOption();

        $this->registerClientScript();

        if ($this->popup == 1) {
            $template = $this->template[1];
        } else {
            $template = $this->template[0];
        }
        echo strtr($template, [
            '{input}' => $input,
            '{url}' => $this->buildFullUrl(),
            '{select}' => Kant::t('kant', 'Select')
        ]);
    }

    /**
     * Register client scripts
     */
    protected function registerClientScript()
    {
        FilemanagerAsset::register($this->view);
        FancyboxAsset::register($this->view);

        if ($this->popup == 1) {
            $script = new JsExpression("function open_popup(url){var w=880;var h=570;var l=Math.floor((screen.width-w)/2);var t=Math.floor((screen.height-h)/2);var win=window.open(url,'ResponsiveFilemanager',\"scrollbars=1,width=\"+w+\",height=\"+h+\",top=\"+t+\",left=\"+l)};$(\".btn-popup-filemanager\").click(function(){open_popup($(this).data('url'));});");
        } else {
            $script = new JsExpression("$('.btn-iframe').fancybox({'width':880,'height':870,'type':'iframe', fitToView:false,'autoScale':false,autoSize: false});");
        }

        $this->view->registerJs($script);
    }

    protected function getPopupOption()
    {
        return !empty($this->clientOptions['popup']) ? intval($this->clientOptions['popup']) : 0;
    }

    protected function buildFullUrl()
    {
        $clientOptions = $this->clientOptions;
        $url = $clientOptions['url'];
        unset($clientOptions['url']);
        $clientOptions['fieldid'] = $this->options['id'];
        if (strpos($url, '?')) {
            $fullUrl = $url . "&" . http_build_query($clientOptions);
        } else {
            $fullUrl = $url . "?" . http_build_query($clientOptions);
        }
        return $fullUrl;
    }
}