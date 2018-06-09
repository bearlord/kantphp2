<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Exception;

/**
 * ViewNotFoundException represents an exception caused by view file not found.
 *
 * @author Alexander Makarov
 * @since 2.0.10
 */
class ViewNotFoundException extends InvalidParamException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'View not Found';
    }
}
