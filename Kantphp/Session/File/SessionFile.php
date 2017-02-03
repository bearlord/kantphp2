<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Session\File;

use Kant\Secure\Crypt\Crypt_AES;

/**
 * Session File
 * 
 * @access private
 * @static
 * @version 1.1
 * @since version 1.1
 */
class SessionFile {

    private $_sessionPath;
    protected $sidpre = 'sess_';
    //Session setting: gc_maxlifetime, auth_key;
    private $_setting;

    public function __construct($setting) {
        $this->_setting = $setting;
        $this->_sessionPath = CACHE_PATH . 'Session' . DIRECTORY_SEPARATOR . 'SessionFile' . DIRECTORY_SEPARATOR;
        $this->_setSessionModule();
    }

    /**
     * Set Session Module
     * 
     */
    private function _setSessionModule() {
        session_module_name('user');
        session_set_save_handler(
                array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc')
        );
        register_shutdown_function('session_write_close');
        session_start();
    }

    /**
     * Open SESSION
     * 
     * @static
     * @return boolean
     */
    public function open() {
        return true;
    }

    /**
     * Close Session
     * 
     * @static
     * @return type
     */
    public function close() {
        $maxlifetime = $this->_setting['maxlifetime'] ? $this->_setting['maxlifetime'] : ini_get('session.gc_maxlifetime');
        return self::gc($maxlifetime);
    }

    /**
     * READ SESSION
     * 
     * @static
     * @param string $sid
     * @return string
     */
    public function read($sid) {
        $file = $this->_sessionPath . $this->sidpre . $sid;
        if (file_exists($file)) {
            $data = file_get_contents($this->_sessionPath . 'sess_' . $sid);
            return $data;
        }
    }

    /**
     * Write SESSION
     * 
     * @static
     * @param string $sid
     * @param string $data
     * @return boolean
     */
    public function write($sid, $data) {
        $file = $this->_sessionPath . 'sess_' . $sid;
        $file_size = file_put_contents($file, $data, LOCK_EX);
        return $file_size ? $file_size : 'false';
    }

    /**
     * Destory SESSION
     * 
     * @param string $sid
     * @return boolean
     */
    public function destroy($sid) {
        $file = $this->_sessionPath . 'sess_' . $sid;
        if (file_exists($file)) {
            unlink($file);
        }
        return true;
    }

    /**
     *  Session Garbage Collector
     * 
     * @param type $maxlifetime
     * @return boolean
     */
    public function gc($maxlifetime) {
        foreach (glob($this->_sessionPath . 'sess_') as $file) {
            if (filemtime($file) < time() - $maxlifetime && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }

}

?>
