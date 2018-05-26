<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kant\Exception;

use BadMethodCallException;

/**
 * UnknownMethodException represents an exception caused by accessing an unknown object method.
 */
class UnknownMethodException extends Exception
{

    /**
     *
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Method';
    }
}
