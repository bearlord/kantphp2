<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Exception;

use Kant\Exception\Exception;

/**
 * InvalidValueException represents an exception caused by a function returning a value of unexpected type.
 */
class InvalidValueException extends Exception
{

    /**
     *
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Return Value';
    }
}
