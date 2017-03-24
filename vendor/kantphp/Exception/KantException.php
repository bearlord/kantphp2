<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Exception;

use Exception;
use Kant\Log\Log;

class KantException extends Exception {

    /**
     * @var null|Exception
     */
    private $_previous = null;

    /**
     * Construct the exception
     *
     * @param  string $msg
     * @param  int $code
     * @param  Exception $previous
     * @return void
     */
    public function __construct($msg = '', $code = 0, Exception $previous = null) {
        $debug = Kant::$app->config->get('debug');
        if ($debug == false) {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
            header("Status: 404 Not Found");
            header("X-Powered-By: KantPHP Framework");
            echo '404 File Not Found!';
            exit();
        }
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($msg, (int) $code);
            $this->_previous = $previous;
        } else {
            $error = array();
            $error['message'] = $msg;
            $trace = $this->getTrace();
            if ('E' == $trace[0]['function']) {
                $error['file'] = $trace[0]['file'];
                $error['line'] = $trace[0]['line'];
            } else {
                $error['file'] = $this->getFile();
                $error['line'] = $this->getLine();
            }
            $error['trace'] = $this->getTraceAsString();
            $exceptionFile = KANT_PATH . 'View/system/exception.php';
            include $exceptionFile;

            exit();
//            parent::__construct($msg, (int) $code, $previous);
        }
    }

    /**
     * Overloading
     *
     * For PHP < 5.3.0, provides access to the getPrevious() method.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, array $args) {
        if ('getprevious' == strtolower($method)) {
            return $this->_getPrevious();
        }
        return null;
    }

    /**
     * String representation of the exception
     *
     * @return string
     */
    public function __toString() {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if (null !== ($e = $this->getPrevious())) {
                return $e->__toString()
                        . "\n\nNext "
                        . parent::__toString();
            }
        }
        return parent::__toString();
    }

    /**
     * Returns previous Exception
     *
     * @return Exception|null
     */
    protected function _getPrevious() {
        return $this->_previous;
    }

}

?>
