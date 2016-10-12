<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
function addslashess($value) {
    if (is_array($value)) {
        $value = array_map('addslashess', $value);
    } else {
        $value = addslashes($value);
    }
    return $value;
}

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

function strcut($str, $start = 0, $offset = '') {
    $j = 0;
    $cn = 0;
    $substr = "";
    if (!$offset)
        $offset = strlen($str);
    while ($cn < $start) {
        if (ord($str{$j}) >= 0x80 && ord($str{$j}) <= 0xff)
            $j = $j + 3;
        else
            $j++;
        $cn++;
    }
    $i = $j;
    $exp = 0;
    while ($exp < $offset) {
        if (ord($str{$i}) >= 0x80 && ord($str{$i}) < 0xff) {
            $substr.= substr($str, $i, 3);
            $i = $i + 3;
        } else {
            $substr.=$str{$i};
            $i++;
        }
        $exp++;
    }
    return $substr;
}

function unserializesession($data) {
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
        if (!is_null($lastOffset)) {
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

if (!function_exists('data_get')) {

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string|array  $key
     * @param  mixed   $default
     * @return mixed
     */
    function data_get($target, $key, $default = null) {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return value($default);
                }

                $result = Arr::pluck($target, $key);

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }

}

if (!function_exists('data_set')) {

    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true) {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        }

        return $target;
    }

}
?>