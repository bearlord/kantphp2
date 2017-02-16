<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Session\Driver\Mysql;

class Session implements \SessionHandlerInterface {

    protected $sidpre = 'sess_';
    //Session setting: gc_maxlifetime, auth_key;
    private $_setting;

    public function __construct($setting) {
        $this->_setting = $setting;
        self::_setSessionModule();
    }

    /**
     * Set Session Module
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
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @return boolean
     */
    public function open($save_path = '', $name = '') {
        return true;
    }

    /**
     * Close the session
     * 
     * bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure).
     */
    public function close() {
        $maxlifetime = $this->_setting['maxlifetime'] ? $this->_setting['maxlifetime'] : ini_get('session.gc_maxlifetime');
        return self::gc($maxlifetime);
    }

    /**
     *  * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * 
     * @param string $session_id
     * @return string an encoded string of the read data. If nothing was read, it must return an empty string. 
     */
    public function read($session_id) {
        $sessionid = $this->sidpre . $session_id;
        $row = Model::find()->where([
                    'sessionid' => $sessionid
                ])->asArray()->one();
        return $row;
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * 
     * @param string $session_id
     * @param string $data
     * @return boolean
     */
    public function write($session_id, $data) {
        $sessionid = $this->sidpre . $session_id;
        $session = Model::find()->where([
                    'sessionid' => $sessionid
                ])->one();
        if (!$session) {
            $session = new Model();
        }
        $session->sessionid = $sessionid;
        $session->data = $data;
        $session->lastvisit = time();
        $session->ip = get_client_ip();
        return $session->save();
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * 
     * @param string $session_id
     * @return @return bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure).
     */
    public function destroy($session_id) {
        return Model::deleteAll([
                    'sessionid' => $this->sidpre . $session_id
        ]);
    }

    /**
     * Cleanup old sessions
     * Sessions that have not updated for the last <i>maxlifetime</i> seconds will be removed.
     * 
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param type $maxlifetime
     * @return bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure).
     */
    public function gc($maxlifetime) {
        return Model::deleteAll(
                        "lastvisit < :expiretime", [':expiretime' => time() - $maxlifetime]
        );
    }

}

?>
