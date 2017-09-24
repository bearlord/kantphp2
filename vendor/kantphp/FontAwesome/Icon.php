<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\FontAwesome;

use Kant\Helper\Html;
use Kant\Helper\ArrayHelper;

class Icon
{

    /**
     *
     * @deprecated
     *
     * @var string
     */
    public static $defaultTag = 'i';

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

    public function __construct($name, $options = [])
    {
        Html::addCssClass($options, FontAwesome::$cssPrefix);
        if (! empty($name)) {
            Html::addCssClass($options, FontAwesome::$cssPrefix . '-' . $name);
        }
        $this->options = $options;
    }

    public function __toString()
    {
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'i');
        return Html::tag($tag, null, $options);
    }

    /**
     * Inverse
     *
     * @return self
     */
    public function inverse()
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-inverse');
    }

    /**
     * Spin
     *
     * @return self
     */
    public function spin()
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-spin');
    }

    /**
     * Fixed Width
     *
     * @return self
     */
    public function fixedWidth()
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-fw');
    }

    /**
     * Li
     *
     * @return self
     */
    public function li()
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-li');
    }

    /**
     * Border
     *
     * @return self
     */
    public function border()
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-border');
    }

    /**
     * Pull left
     *
     * @return self
     */
    public function pullLeft()
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-pull-left');
    }

    /**
     * Pull right
     *
     * @return self
     */
    public function pullRight()
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-pull-right');
    }

    /**
     * Size
     *
     * @param string $value            
     * @return self
     */
    public function size($value)
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-' . $value, in_array((string) $value, [
            FontAwesome::SIZE_LARGE,
            FontAwesome::SIZE_2X,
            FontAwesome::SIZE_3X,
            FontAwesome::SIZE_4X,
            FontAwesome::SIZE_5X
        ], true), sprintf('%s - invalid value. Use one of the constants: %s.', 'FA::size()', 'FA::SIZE_LARGE, FA::SIZE_2X, FA::SIZE_3X, FA::SIZE_4X, FA::SIZE_5X'));
    }

    /**
     * Rotate
     *
     * @param string $value            
     * @return self
     */
    public function rotate($value)
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-rotate-' . $value, in_array((string) $value, [
            FontAwesome::ROTATE_90,
            FontAwesome::ROTATE_180,
            FontAwesome::ROTATE_270
        ], true), sprintf('%s - invalid value. Use one of the constants: %s.', 'FA::rotate()', 'FA::ROTATE_90, FA::ROTATE_180, FA::ROTATE_270'));
    }

    /**
     * Flip
     *
     * @param string $value            
     * @return self
     */
    public function flip($value)
    {
        return $this->addCssClass(FontAwesome::$cssPrefix . '-flip-' . $value, in_array((string) $value, [
            FontAwesome::FLIP_HORIZONTAL,
            FontAwesome::FLIP_VERTICAL
        ], true), sprintf('%s - invalid value. Use one of the constants: %s.', 'FA::flip()', 'FA::FLIP_HORIZONTAL, FA::FLIP_VERTICAL'));
    }

    /**
     * Tag
     *
     * Change html tag.
     * 
     * @param string $tag            
     * @return static
     */
    public function tag($tag)
    {
        $this->tag = $tag;
        $this->options['tag'] = $tag;
        return $this;
    }

    /**
     * Add class
     *
     * @param string $class            
     * @param bool $condition            
     * @param string|bool $throw            
     * @return \Kant\FontAwesome\Icon
     */
    public function addCssClass($class, $condition = true, $throw = false)
    {
        if ($condition === false) {
            if (! empty($throw)) {
                $message = ! is_string($throw) ? 'Condition is false' : $throw;
                
                throw new \yii\base\InvalidConfigException($message);
            }
        } else {
            Html::addCssClass($this->options, $class);
        }
        
        return $this;
    }

    /**
     * Render
     *
     * @param string|null $tag            
     * @param string|null $content            
     * @param array $options            
     * @return string
     */
    public function render($tag = null, $content = null, $options = [])
    {
        $tag = empty($tag) ? (empty($this->tag) ? static::$defaultTag : $this->tag) : $tag;
        $options = array_merge($this->options, $options);
        return Html::tag($tag, $content, $options);
    }
}
