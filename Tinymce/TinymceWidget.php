<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Tinymce;

use Kant\Kant;
use Kant\View\View;
use Kant\Widget\InputWidget;
use Kant\Tinymce\TinymceAsset;
use Kant\Helper\Html;
use Kant\Helper\Json;
use Kant\Helper\JsExpression;

class TinymceWidget extends InputWidget
{

    public $clientOptions = [];

    public $convention = [
        'plugins' => 'advlist autolink link image lists charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking spellchecker table contextmenu directionality emoticons paste textcolor',
        'toolbar' => 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect | filemanager image | media | link unlink anchor | print preview code  | forecolor backcolor'
    ];

    public function init()
    {
        parent::init();
        $this->id = $this->options['id'];
    }

    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            return Html::activeTextarea($this->model, $this->attribute, [
                'id' => $this->id
            ]);
        } else {
            return Html::textarea($this->id, $this->value, [
                'id' => $this->id
            ]);
        }
    }

    /**
     * Register client scripts
     */
    protected function registerClientScript()
    {
        $this->formatClientOptions();
        TinymceAsset::register($this->view);
        TinymceLangAsset::register($this->view);
        $clientOptions = Json::encode($this->clientOptions);
        $script = "tinymce.init(" . $clientOptions . ");";
        $this->view->registerJs($script);
    }

    protected function formatClientOptions()
    {
        $this->setFilemanager();
        $this->clientOptions['selector'] = "#" . $this->id;
        $this->clientOptions = array_merge($this->convention, $this->clientOptions);
    }

    protected function setFilemanager()
    {
        if (! empty($this->clientOptions['filemanager'])) {
            $this->convention['plugins'] .= ' filemanager';
            $this->convention['toolbar'] .= ' |filemanager';
            $this->convention['image_advtab'] = true;
            $this->convention['relative_urls'] = false;
            $this->convention['external_filemanager_path'] = $this->clientOptions['filemanager'];
            $this->convention['file_picker_types'] = 'file image media';
            $this->convention['filemanager_title'] = Kant::t('kant', 'File Manager');
            $this->convention['file_picker_callback'] = new JsExpression('function(cb,value,meta){var width=window.innerWidth-30;var height=window.innerHeight-60;if(width>1800)width=1800;if(height>1200)height=1200;if(width>600){var width_reduce=(width-20)%138;width=width-width_reduce+10}var urltype=meta.filetype;if(urltype===\'file\'){urltype=\'files\'}var title="FileManager";if(typeof this.settings.filemanager_title!=="undefined"&&this.settings.filemanager_title){title=this.settings.filemanager_title}var akey="key";if(typeof this.settings.filemanager_access_key!=="undefined"&&this.settings.filemanager_access_key){akey=this.settings.filemanager_access_key}var sort_by="";if(typeof this.settings.filemanager_sort_by!=="undefined"&&this.settings.filemanager_sort_by){sort_by="&sort_by="+this.settings.filemanager_sort_by}var descending="false";if(typeof this.settings.filemanager_descending!=="undefined"&&this.settings.filemanager_descending){descending=this.settings.filemanager_descending}var fldr="";if(typeof this.settings.filemanager_subfolder!=="undefined"&&this.settings.filemanager_subfolder){fldr="&fldr="+this.settings.filemanager_subfolder}var crossdomain="";if(typeof this.settings.filemanager_crossdomain!=="undefined"&&this.settings.filemanager_crossdomain){crossdomain="&crossdomain=1";if(window.addEventListener){window.addEventListener("message",filemanager_onMessage,false)}else{window.attachEvent("onmessage",filemanager_onMessage)}}tinymce.activeEditor.windowManager.open({title:title,file:this.settings.external_filemanager_path+"?type="+urltype+"&descending="+descending+sort_by+fldr+crossdomain+"&lang="+this.settings.language+"&akey="+akey,width:width,height:height,resizable:true,maximizable:true,inline:1},{setUrl:function(url){var name=url.substr(url.lastIndexOf("/")+1);var params={};if(meta.filetype=="image"){params={alt:name,target:"_blank"}}else if(meta.filetype=="file"){params={text:name,"title":name,target:"_blank"}}cb(url,params);}})}');
        }
    }
}
