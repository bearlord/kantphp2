<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Exception;

use Kant\Exception\InvalidConfigException;

/**
 * NotInstantiableException represents an exception caused by incorrect dependency injection container
 * configuration or usage.
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.9
 */
class NotInstantiableException extends InvalidConfigException
{

    /**
     * @inheritdoc
     */
    public function __construct($class, $message = null, $code = 0, \Exception $previous = null)
    {
        if ($message === null) {
            $message = "Can not instantiate $class.";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     *
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not instantiable';
    }
}
