<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Exception;

/**
 * ForbiddenHttpException represents a "Forbidden" HTTP exception with status code 403.
 *
 * Use this exception when a user has been authenticated but is not allowed to
 * perform the requested action. If the user is not authenticated, consider
 * using a 401 [[UnauthorizedHttpException]]. If you do not want to
 * expose authorization information to the user, it is valid to respond with a
 * 404 [[NotFoundHttpException]].
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.4
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class ForbiddenHttpException extends HttpException
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
        parent::__construct(403, $message, $code, $previous);
    }
}