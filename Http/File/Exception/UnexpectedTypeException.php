<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http\File\Exception;

class UnexpectedTypeException extends FileException
{

    public function __construct($value, $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type %s, %s given', $expectedType, is_object($value) ? get_class($value) : gettype($value)));
    }
}
