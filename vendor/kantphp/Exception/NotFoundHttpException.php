<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kant\Exception;

class NotFoundHttpException extends HttpException
{

    /**
     * Constructor.
     *
     * @param string $message
     *            The internal exception message
     * @param \Exception $previous
     *            The previous exception
     * @param int $code
     *            The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
