<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Bootstrap;

use Kant\Kant;
use Kant\Helper\Json;

/**
 * BootstrapWidgetTrait is the trait, which provides basic for all bootstrap widgets features.
 *
 * Note: class, which uses this trait must declare public field named `options` with the array default value:
 *
 * ```php
 * class MyWidget extends Kant\Widget\Widget
 * {
 * use BootstrapWidgetTrait;
 *
 * public $options = [];
 * }
 * ```
 *
 * This field is not present in the trait in order to avoid possible PHP Fatal error on definition conflict.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.6
 */
trait BootstrapWidgetTrait
{

    /**
     *
     * @var array the options for the underlying Bootstrap JS plugin.
     *      Please refer to the corresponding Bootstrap plugin Web page for possible options.
     *      For example, [this page](http://getbootstrap.com/javascript/#modals) shows
     *      how to use the "Modal" plugin and the supported options (e.g. "remote").
     */
    public $clientOptions = [];

    /**
     *
     * @var array the event handlers for the underlying Bootstrap JS plugin.
     *      Please refer to the corresponding Bootstrap plugin Web page for possible events.
     *      For example, [this page](http://getbootstrap.com/javascript/#modals) shows
     *      how to use the "Modal" plugin and the supported events (e.g. "shown").
     */
    public $clientEvents = [];

    /**
     * Initializes the widget.
     * This method will register the bootstrap asset bundle. If you override this method,
     * make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * Registers a specific Bootstrap plugin and the related events
     * 
     * @param string $name
     *            the name of the Bootstrap plugin
     */
    protected function registerPlugin($name)
    {
        $view = $this->getView();
        
        BootstrapPluginAsset::register($view);
        
        $id = $this->options['id'];
        
        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
            $js = "jQuery('#$id').$name($options);";
            $view->registerJs($js);
        }
        
        $this->registerClientEvents();
    }

    /**
     * Registers JS event handlers that are listed in [[clientEvents]].
     * 
     * @since 2.0.2
     */
    protected function registerClientEvents()
    {
        if (!empty($this->clientEvents)) {
            $id = $this->options['id'];
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }

    /**
     *
     * @return \Kant\View\View the view object that can be used to render views or view files.
     * @see Kant\View\Widget::getView()
     */
    abstract function getView();
}
