<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Session;

use Kant\Secure\Crypt\Crypt_AES;

/**
 * Session Memcache
 * 
 * @access private
 * @static
 * @version 1.1
 * @since version 1.1
 */
class SessionMemcache {

    //Session setting: gc_maxlifetime, auth_key;
    private $_setting;
    //Session Model
    private $_sessM;

    public function __construct($setting) {
        $this->_setting = $setting;
        require_once KANT_PATH . 'Session/Sqlite/SessionSqliteModel.php';
        $this->_sessM = new SessionSqliteModel();
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

    public function open() {
        return true;
    }

    public function close() {
        $maxlifetime = $this->_setting['maxlifetime'] ? $this->_setting['maxlifetime'] : ini_get('session.gc_maxlifetime');
        return self::gc($maxlifetime);
    }

    /**
     * READ SESSION
     * 
     * @param string $sid
     * @return string
     */
    public function read($sid) {
        $sessionid = 'sess_' . $sid;
        $row = $this->_sessM->read($sessionid, '', 'sessionid');
        if ($row) {
            $row = $row[0];
            require_once KANT_PATH . 'Secure/phpseclib/bootstrap.php';
            $crypt = new Crypt_AES();
            $crypt->setKey($this->_setting['auth_key']);
            $secure_data = $row['data'];
            //BASE64 decode, AES decrypt
            $data = $crypt->decrypt(base64_decode($secure_data));
            return $data;
        }
    }

    /**
     * Write Session
     * 
     * @param string $sid
     * @param string $data
     * @return boolean
     */
    public function write($sid, $data) {
        $sessionid = 'sess_' . $sid;
        require_once KANT_PATH . 'Secure/phpseclib/bootstrap.php';
        $crypt = new Crypt_AES();
        var_dump($this);
        $crypt->setKey($this->_setting['auth_key']);
        //AES encrypt, BASE64 encode
        $secure_data = base64_encode($crypt->encrypt($data));
        $exist = $this->_sessM->read($sessionid, '', 'sessionid');
        if (!$exist) {
            $row = $this->_sessM->save(array(
                'sessionid' => $sessionid,
                'data' => $secure_data,
                'lastvisit' => time(),
                'ip' => get_ip()
            ));
        } else {
            $row = $this->_sessM->save(array(
                'data' => $secure_data,
                'lastvisit' => time(),
                    ), $sessionid);
        }

        return $row ? 'true' : 'false';
    }

    public function destroy($sid) {
        $this->_sessM->delete($sid);
        return true;
    }

    public function gc($maxlifetime) {
        $expiretime = time() - $maxlifetime;
        $this->_sessM->deleteExpire($expiretime);
        return true;
    }

}

?>
