<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Database;

class Exception extends \Kant\Exception\Exception
{

    /**
     *
     * @var array the error info provided by a PDO exception. This is the same as returned
     *      by [PDO::errorInfo](http://www.php.net/manual/en/pdo.errorinfo.php).
     */
    public $errorInfo = [];

    /**
     * Constructor.
     * 
     * @param string $message
     *            PDO error message
     * @param array $errorInfo
     *            PDO error info
     * @param integer $code
     *            PDO error code
     * @param \Exception $previous
     *            The previous exception used for the exception chaining.
     */
    public function __construct($message, $errorInfo = [], $code = 0, \Exception $previous = null)
    {
        $this->errorInfo = $errorInfo;
        parent::__construct($message, $code, $previous);
    }

    /**
     *
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Database Exception';
    }

    /**
     *
     * @return string readable representation of exception
     */
    public function __toString()
    {
        return parent::__toString() . PHP_EOL . 'Additional Information:' . PHP_EOL . print_r($this->errorInfo, true);
    }
}
