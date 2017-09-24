<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
use Kant\Kant;

if (! function_exists('hash')) {

    /**
     *
     * Encode decode function
     *
     * @param
     *            string string
     * @param
     *            operation string
     * @param
     *            key string
     * @param
     *            expiry boolean
     * @return string
     */
    function hash($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $key = sha1($key ? $key : \Kant\Kant::$app->config->get('auth_key'));
        
        // Key a is used to participate in encryption and decryption
        $keya = sha1(substr($key, 0, 20));
        // Key b is used to validate data integrity
        $keyb = sha1(substr($key, 20, 20));
        // Key c is used to generate the ciphertext
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(sha1(microtime()), - $ckey_length)) : '';
        
        // Key to participate in operation
        $cryptkey = $keya . sha1($keya . $keyc);
        $key_length = strlen($cryptkey);
        
        // Plain text, top 10 is used to preserve time stamps, decrypt to verify data validity, 10 to 30 to save $keyb (key b), decryption through the key data integrity verification.
        // Decoding will start from $ckey_length position, since secret top $ckey_length save the dynamic key, to ensure that the decryption right.
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(sha1($string . $keyb), 0, 20) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 239);
        $rndkey = array();
        
        // Key book
        for ($i = 0; $i <= 239; $i ++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        
        // With a fixed algorithm, disrupted key book, increase randomness seems very complicated, in fact, does not increase strength of the ciphertext.
        for ($j = $i = 0; $i < 240; $i ++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 240;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // Core part encryption and decryption
        for ($a = $j = $i = 0; $i < $string_length; $i ++) {
            $a = ($a + 1) % 240;
            $j = ($j + $box[$a]) % 240;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // Xor the key obtained from the key book, go into character
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 240]));
        }
        
        if ($operation == 'DECODE') {
            // substr($result, 0, 10) == 0 Erify data validity
            // substr($result, 0, 10) - time() > 0 Erify data validity
            // substr($result, 10, 20) == substr(sha1(substr($result, 30).$keyb), 0, 20) Verify data integrity
            // Verification data validation, see the unencrypted clear text format
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 20) == substr(sha1(substr($result, 30) . $keyb), 0, 20)) {
                return substr($result, 30);
            } else {
                return '';
            }
        } else {
            // Key is saved in dynamic in the ciphertext, which is why the same plaintext, producing different ciphertext can be decrypted.
            // Because the encrypted ciphertext might be special characters, the replication process may be lost, so using base64 encoding.
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}
if (! function_exists('csrf_token')) {

    /**
     * Get the CSRF token value.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    function csrf_token()
    {
        $session = Kant::$app->session;
        
        if (isset($session)) {
            return $session->getToken();
        }
        
        throw new RuntimeException('Application session store not set.');
    }
}

if (! function_exists('addslashess')) {

    function addslashess($value)
    {
        if (is_array($value)) {
            $value = array_map('addslashess', $value);
        } else {
            $value = addslashes($value);
        }
        return $value;
    }
}

if (! function_exists('get_client_ip')) {

    /**
     *
     * Get client IP
     *
     * @return string
     */
    function get_client_ip()
    {
        $onlineip = null;
        $onlineipmatches = array();
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        $onlineip = addslashes($onlineip);
        @preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
        $onlineip = ! empty($onlineipmatches[0]) ? $onlineipmatches[0] : 'unknown';
        unset($onlineipmatches);
        return $onlineip;
    }
}

if (function_exists('random')) {

    function random($bit = 4, $type = "mix")
    {
        $code = '';
        if (in_array($type, array(
            'letter',
            'digit',
            'mix'
        )) == false) {
            return false;
        }
        $_charset = array(
            'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'digit' => '0123456789',
            'mix' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'
        );
        $charset = $_charset[$type];
        $charset_len = strlen($charset) - 1;
        for ($i = 0; $i < $bit; $i ++) {
            $code .= $charset[rand(1, $charset_len)];
        }
        return $code;
    }
}

if (! function_exists('array_wrap')) {

    /**
     * If the given value is not an array, wrap it in one.
     *
     * @param mixed $value            
     * @return array
     */
    function array_wrap($value)
    {
        return ! is_array($value) ? [
            $value
        ] : $value;
    }
}

function strcut($str, $start = 0, $offset = '')
{
    $j = 0;
    $cn = 0;
    $substr = "";
    if (! $offset)
        $offset = strlen($str);
    while ($cn < $start) {
        if (ord($str{$j}) >= 0x80 && ord($str{$j}) <= 0xff)
            $j = $j + 3;
        else
            $j ++;
        $cn ++;
    }
    $i = $j;
    $exp = 0;
    while ($exp < $offset) {
        if (ord($str{$i}) >= 0x80 && ord($str{$i}) < 0xff) {
            $substr .= substr($str, $i, 3);
            $i = $i + 3;
        } else {
            $substr .= $str{$i};
            $i ++;
        }
        $exp ++;
    }
    return $substr;
}

if (! function_exists('unserializesession')) {

    function unserializesession($data)
    {
        if (strlen($data) == 0) {
            return array();
        }
        
        // match all the session keys and offsets
        preg_match_all('/(^|;|\})([a-zA-Z0-9_]+)\|/i', $data, $matchesarray, PREG_OFFSET_CAPTURE);
        
        $returnArray = array();
        
        $lastOffset = null;
        $currentKey = '';
        foreach ($matchesarray[2] as $value) {
            $offset = $value[1];
            if (! is_null($lastOffset)) {
                $valueText = substr($data, $lastOffset, $offset - $lastOffset);
                $returnArray[$currentKey] = unserialize($valueText);
            }
            $currentKey = $value[0];
            
            $lastOffset = $offset + strlen($currentKey) + 1;
        }
        
        $valueText = substr($data, $lastOffset);
        $returnArray[$currentKey] = unserialize($valueText);
        
        return $returnArray;
    }
}

if (! function_exists('value')) {

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value            
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('tap')) {

    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param mixed $value            
     * @param callable $callback            
     * @return mixed
     */
    function tap($value, $callback)
    {
        $callback($value);
        
        return $value;
    }
}
?>