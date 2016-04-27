<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cookie;

use Kant\Registry\KantRegistry;

!defined('IN_KANT') && exit('Access Denied');

final class Cookie {

    private static $_cookie;

    /**
     *
     * @example: 
     * array('cookie_domain' => '','cookie_path' => '/','cookie_pre' => 'kantphp_','cookie_ttl' => 0)
     */
    private $_cookieConfig = array();

    public function __construct() {
        
    }

    /**
     *
     * Get instantce of the final object
     *
     * @param cache_config string
     * @return object on success
     */
    public static function getInstance($cookieConfig = '') {
        $config = KantRegistry::get('config');
        if ($cookieConfig == '') {
            $config = KantRegistry::get('config');
            $cookieConfig = $config['cookie'];
        }
        if (self::$_cookie == '') {
            self::$_cookie = new self();
        }
        if ($cookieConfig != '' && $cookieConfig != self::$_cookie->_cookieConfig) {
            self::$_cookie->_cookieConfig = array_merge($cookieConfig, self::$_cookie->_cookieConfig);
        }
        return self::$_cookie;
    }

    /**
     *
     * Encode decode function
     *
     * @param string string
     * @param operation string
     * @param key string
     * @param expiry boolean
     * @return string
     */
    private function _sysAutch($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        $ckey_length = 4;
        $key = sha1($key ? $key : $this->_cookieConfig['auth_key']);
        // 密匙a会参与加解密
        $keya = sha1(substr($key, 0, 20));
        // 密匙b会用来做数据完整性验证
        $keyb = sha1(substr($key, 20, 20));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(sha1(microtime()), -$ckey_length)) : '';

        // 参与运算的密匙
        $cryptkey = $keya . sha1($keya . $keyc);
        $key_length = strlen($cryptkey); //80行
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到30位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(sha1($string . $keyb), 0, 20) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 239);
        $rndkey = array();

        // 产生密匙簿
        for ($i = 0; $i <= 239; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 240; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 240;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 240;
            $j = ($j + $box[$a]) % 240;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 240]));
        }

        if ($operation == 'DECODE') {
            // substr($result, 0, 10) == 0 验证数据有效性
            // substr($result, 0, 10) - time() > 0 验证数据有效性
            // substr($result, 10, 20) == substr(sha1(substr($result, 30).$keyb), 0, 20) 验证数据完整性
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 20) == substr(sha1(substr($result, 30) . $keyb), 0, 20)) {
                return substr($result, 30);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     *
     * Set cookie
     *
     * @param var string
     * @param value string
     * @param time integer
     */
    public function set($var, $value = '', $time = 0) {
        //If $time exists,set cookie time is $time,and if $time is null,set cookie time expired
        $time = $time > 0 ? (time() + $time) : ($value == '' ? time() - 31536000 : $this->_cookieConfig['cookie_ttl']);
        $s = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
        $var = $this->_cookieConfig['cookie_pre'] . $var;
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                setcookie($var . '[' . $k . ']', self::_sysAutch($v, 'ENCODE'), $time, $this->_cookieConfig['cookie_path'], $this->_cookieConfig['cookie_domain'], $s);
            }
        } else {
            if (isset($_COOKIE[$var]) && is_array($_COOKIE[$var])) {
                foreach ($_COOKIE[$var] as $k => $v) {
                    setcookie($var . '[' . $k . ']', !empty($value) ? self::_sysAutch($value, 'ENCODE') : '', $time, $this->_cookieConfig['cookie_path'], $this->_cookieConfig['cookie_domain'], $s);
                }
            } else {
                setcookie($var, !empty($value) ? self::_sysAutch($value, 'ENCODE') : '', $time, $this->_cookieConfig['cookie_path'], $this->_cookieConfig['cookie_domain'], $s);
            }
        }
    }

    /**
     *  Get cookie
     * 
     * @param string $var
     * @param type $default
     * @return type
     */
    public function get($var, $default = '') {
        $var = $this->_cookieConfig['cookie_pre'] . $var;
        if (isset($_COOKIE[$var])) {
            if (is_array($_COOKIE[$var])) {
                foreach ($_COOKIE[$var] as $k => $v) {
                    $cookie[$var][$k] = self::_sysAutch($v, 'DECODE');
                }
            } else {
                $cookie[$var] = self::_sysAutch($_COOKIE[$var], 'DECODE');
            }
            return $cookie[$var];
        } else {
            return $default;
        }
    }

}
