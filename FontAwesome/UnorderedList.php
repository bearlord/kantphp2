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

class UnorderedList
{

    /**
     *
     * @deprecated
     *
     * @var string
     */
    public static $defaultTag = 'ul';

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
    protected $options = [];

    /**
     *
     * @var array
     */
    protected $items = [];

    /**
     *
     * @param array $options            
     */
    public function __construct($options = [])
    {
        Html::addCssClass($options, FontAwesome::$cssPrefix . '-ul');
        $options['item'] = function ($item, $index) {
            return call_user_func($item, $index);
        };
        $this->options = $options;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return Html::ul($this->items, $this->options);
    }

    /**
     *
     * @param string $label            
     * @param array $options            
     * @return static
     */
    public function item($label, $options = [])
    {
        $this->items[] = function ($index) use($label, $options) {
            $tag = ArrayHelper::remove($options, 'tag', 'li');
            $icon = ArrayHelper::remove($options, 'icon');
            $icon = empty($icon) ? null : (is_string($icon) ? (string) (new Icon($icon))->li() : $icon);
            $content = trim($icon . $label);
            return Html::tag($tag, $content, $options);
        };
        
        return $this;
    }

    /**
     *
     * @deprecated Change html tag.
     * @param string $tag            
     * @return static
     * @throws \yii\base\InvalidParamException
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
        $items = $this->items;
        return Html::tag($tag, implode($items), $options);
    }
}
