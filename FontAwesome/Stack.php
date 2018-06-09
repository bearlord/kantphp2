<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\FontAwesome;

use Kant\Helper\ArrayHelper;
use Kant\Helper\Html;

class Stack
{

    /**
     *
     * @deprecated
     *
     * @var string
     */
    public static $defaultTag = 'span';

    /**
     *
     * @deprecated
     *
     * @var string
     */
    private $tag;

    /**
     *
     * @var array
     */
    private $options = [];

    /**
     *
     * @var Icon
     */
    private $icon_front;

    /**
     *
     * @var Icon
     */
    private $icon_back;

    /**
     *
     * @param array $options            
     */
    public function __construct($options = [])
    {
        Html::addCssClass($options, FontAwesome::$cssPrefix . '-stack');
        $this->options = $options;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'span');
        $template = ArrayHelper::remove($options, 'template', '{back}{front}');
        $icon_back = $this->icon_back instanceof Icon ? $this->icon_back->addCssClass(FontAwesome::$cssPrefix . '-stack-2x') : null;
        $icon_front = $this->icon_front instanceof Icon ? $this->icon_front->addCssClass(FontAwesome::$cssPrefix . '-stack-1x') : null;
        $content = str_replace([
            '{back}',
            '{front}'
        ], [
            $icon_back,
            $icon_front
        ], $template);
        return Html::tag($tag, $content, $options);
    }

    /**
     *
     * @param string|Icon $icon            
     * @param array $options            
     * @return self
     */
    public function icon($icon, $options = [])
    {
        if (is_string($icon)) {
            $icon = new Icon($icon, $options);
        }
        
        $this->icon_front = $icon;
        return $this;
    }

    /**
     *
     * @param string|Icon $icon            
     * @param array $options            
     * @return self
     */
    public function on($icon, $options = [])
    {
        if (is_string($icon)) {
            $icon = new Icon($icon, $options);
        }
        
        $this->icon_back = $icon;
        return $this;
    }

    /**
     *
     * Change html tag.
     *
     * @param string $tag            
     * @return static
     *
     */
    public function tag($tag)
    {
        $this->tag = $tag;
        $this->options['tag'] = $tag;
        return $this;
    }

    /**
     *
     * @deprecated
     *
     * @param string|null $tag            
     * @param array $options            
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function render($tag = null, $options = [])
    {
        $tag = empty($tag) ? (empty($this->tag) ? static::$defaultTag : $this->tag) : $tag;
        $options = array_merge($this->options, $options);
        $template = ArrayHelper::remove($options, 'template', '{back}{front}');
        $icon_back = $this->icon_back instanceof Icon ? $this->icon_back->addCssClass(FontAwesome::$cssPrefix . '-stack-2x') : null;
        $icon_front = $this->icon_front instanceof Icon ? $this->icon_front->addCssClass(FontAwesome::$cssPrefix . '-stack-1x') : null;
        return Html::tag($tag, str_replace([
            '{back}',
            '{front}'
        ], [
            $icon_back,
            $icon_front
        ], $template), $options);
    }
}
