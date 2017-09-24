<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Exception;

/**
 * ServerErrorHttpException represents an "Internal Server Error" HTTP exception with status code 500.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ServerErrorHttpException extends HttpException
{

    /**
     * Constructor.
     * 
     * @param string $message
     *            error message
     * @param int $code
     *            error code
     * @param \Exception $previous
     *            The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(500, $message, $code, $previous);
    }
}
