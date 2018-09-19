<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Helper;

use Kant\Foundation\BaseObject;

/**
 * JsExpression marks a string as a JavaScript expression.
 *
 * When using [[\Kant\Helper\Json::encode()]] or [[\Kant\Helper\Json::htmlEncode()]] to encode a value, JsonExpression objects
 * will be specially handled and encoded as a JavaScript expression instead of a string.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class JsExpression extends BaseObject
{

    /**
     *
     * @var string the JavaScript expression represented by this object
     */
    public $expression;

    /**
     * Constructor.
     * 
     * @param string $expression
     *            the JavaScript expression represented by this object
     * @param array $config
     *            additional configurations for this object
     */
    public function __construct($expression, $config = [])
    {
        $this->expression = $expression;
        parent::__construct($config);
    }

    /**
     * The PHP magic function converting an object into a string.
     * 
     * @return string the JavaScript expression.
     */
    public function __toString()
    {
        return $this->expression;
    }
}
