<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

/**
 *
 * Get client IP
 *
 * @return string
 */
function get_client_ip() {
    $onlineip = null;
    $onlineipmatches = array();
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] &&
            strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    $onlineip = addslashes($onlineip);
    @preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
    $onlineip = !empty($onlineipmatches[0]) ? $onlineipmatches[0] : 'unknown';
    unset($onlineipmatches);
    return $onlineip;
}

function random($bit = 4, $type = "mix") {
    $code = '';
    if (in_array($type, array('letter', 'digit', 'mix')) == false) {
        return false;
    }
    $_charset = array(
        'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'digit' => '0123456789',
        'mix' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'
    );
    $charset = $_charset[$type];
    $charset_len = strlen($charset) - 1;
    for ($i = 0; $i < $bit; $i++) {
        $code .= $charset[rand(1, $charset_len)];
    }
    return $code;
}

function strcut($string, $length, $dot = '...') {
    $strlen = strlen($string);
    if ($strlen <= $length)
        return $string;
    $string = str_replace(array(' ', '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵', ' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strcut = '';
    if (strtolower(CHARSET) == 'utf-8') {
        $length = intval($length - strlen($dot) - $length / 3);
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
        $strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
    } else {
        $dotlen = strlen($dot);
        $maxi = $length - $dotlen - 1;
        $current_str = '';
        $search_arr = array('&', ' ', '"', "'", '“', '”', '—', '<', '>', '·', '…', '∵');
        $replace_arr = array('&amp;', '&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;', ' ');
        $search_flip = array_flip($search_arr);
        for ($i = 0; $i < $maxi; $i++) {
            $current_str = ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            if (in_array($current_str, $search_arr)) {
                $key = $search_flip[$current_str];
                $current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
            }
            $strcut .= $current_str;
        }
    }
    return $strcut . $dot;
}

?>