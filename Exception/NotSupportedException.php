<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Exception;

/**
 * NotSupportedException represents an exception caused by accessing features that are not supported.
 */
class NotSupportedException extends Exception
{

    /**
     *
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not Supported';
    }
}
