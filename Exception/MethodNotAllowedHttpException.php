<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Exception;

/**
 * Description of MethodNotAllowedHttpException
 *
 * @author Administrator
 */
class MethodNotAllowedHttpException extends HttpException
{

    /**
     * Constructor.
     *
     * @param array $allow
     *            An array of allowed methods
     * @param string $message
     *            The internal exception message
     * @param \Exception $previous
     *            The previous exception
     * @param int $code
     *            The internal exception code
     */
    public function __construct(array $allow, $message = null, \Exception $previous = null, $code = 0)
    {
        $message = "Allow Method: " . strtoupper(implode(', ', $allow));
        parent::__construct(405, $message, $code, $previous);
    }
}
