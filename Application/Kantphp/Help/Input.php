<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Help;
/**
 * Input class
 * 
 * @access public
 * @version 1.1
 * @since version 1.1
 */
class Input {

    private static $_instance;
    private $filter = null;
    private static $_input = array('get', 'post', 'request', 'env', 'server', 'cookie', 'session', 'globals', 'config', 'lang', 'call');
    public static $htmlTags = array(
        'allow' => 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a',
        'ban' => 'html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml',
    );

    public static function getInstance() {
        if (self::$_instance == '') {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function __call($type, $args = array()) {
        $type = strtolower(trim($type));
        if (in_array($type, self::$_input, true)) {
            switch ($type) {
                case 'get': $input = & $_GET;
                    break;
                case 'post': $input = & $_POST;
                    break;
                case 'request': $input = & $_REQUEST;
                    break;
                case 'env': $input = & $_ENV;
                    break;
                case 'server': $input = & $_SERVER;
                    break;
                case 'cookie': $input = & $_COOKIE;
                    break;
                case 'session': $input = & $_SESSION;
                    break;
                case 'globals': $input = & $GLOBALS;
                    break;
                case 'files': $input = & $_FILES;
                    break;
                default:
                    return NULL;
            }
            if ('call' === $input) {
                // 呼叫其他方式的输入数据
                $callback = array_shift($args);
                $params = array_shift($args);
                $data = call_user_func_array($callback, $params);
                if (count($args) === 0) {
                    return $data;
                }
                $filter = isset($args[0]) ? $args[0] : $this->filter;
                if (!empty($filter)) {
                    $data = call_user_func_array($filter, $data);
                }
            } else {
                if (0 == count($args) || empty($args[0])) {
                    return $input;
                } elseif (array_key_exists($args[0], $input)) {
                    // 系统变量
                    $data = $input[$args[0]];
                    $filter = isset($args[1]) ? $args[1] : $this->filter;
                    if (!empty($filter)) {
//                        $data	 =	 call_user_func_array($filter,$data);
                        if (method_exists(__CLASS__, $filter)) {
                            $filter = array(__CLASS__, $filter);
                        }
                        $data = is_array($data) ? call_user_func_array($filter, $data) : call_user_func($filter, $data);
                    }
                } else {
                    // 不存在指定输入
                    $data = isset($args[2]) ? $args[2] : NULL;
                }
            }
            return $data;
        }
    }

    public function filter($filter) {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Quote string with slashes
     * 
     * @param type $string
     * @return type
     */
    public static function addslashes($string) {
        if (!is_array($string)) {
            return addslashes($string);
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = self::addslashes($val);
            }
            return $string;
        }
    }

    /**
     *
     * Convert special characters to HTML entities
     *
     * @param string string,array
     * @return string,array The converted string,array.
     */
    public static function htmlspecialchars($string) {
        if (!is_array($string)) {
            return htmlspecialchars($string);
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = htmlspecialchars($val);
            }
            return $string;
        }
    }

    /**
     * Get the integer value of a variable
     * 
     * @param type $number
     * @return type
     */
    public static function intval($number) {
        if (!is_array($number)) {
            return intval($number);
        } else {
            foreach ($number as $key => $val) {
                $number[$key] = intval($val);
            }
            return $number;
        }
    }

    /**
     *
     * Foramt HTML with htmlspecialchars function
     *
     * @param string string,array;
     * @return string,array The converted string,array.
     */
    public static function textval($string) {
        if (!is_array($string)) {
            $string = is_string($string) ? self::removexss(htmlspecialchars(trim($string), ENT_QUOTES)) : $string;
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = self::textval($val);
            }
        }
        $wu = array('&amp;copy;', '&amp;reg;', '&amp;trade;');
        $rp = array('&copy;', '&reg;', '&trade;');
        return str_replace($wu, $rp, $string);
    }

    /**
     * Un-quotes a quoted string
     * 
     * @param type $string
     * @return type
     */
    public static function stripslashes($string) {
        if (!is_array($string)) {
            $string = is_string($string) ? stripslashes($string) : $string;
            return $string;
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = stripslashes($val);
            }
            return $string;
        }
    }

    /**
     *
     * Filter SQL
     *
     * @param string string
     * @return string string
     */
    public static function stripsql($string) {
        if (!is_array($string)) {
            $pattern_arr = array("/ union /i", "/ select /i", "/ update /i", "/ outfile /i", "/ or /i");
            $replace_arr = array('&nbsp;union&nbsp;', '&nbsp;select&nbsp;', '&nbsp;update&nbsp;', '&nbsp;outfile&nbsp;', '&nbsp;or&nbsp;');
            $string = preg_replace($pattern_arr, $replace_arr, $string);
            return $string;
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = self::stripsql($val);
            }
            return $string;
        }
    }

    /**
     *
     * Format HTML for security reasons
     * 
     * @param string string
     * @return string string
     */
    public static function html($string) {
        $string = self::removexss($string);
        return $string;
    }

    /**
     * xss过滤函数
     *
     * @param $string
     * @return string
     */
    public static function removexss($string) {
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
        $parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $parm = array_merge($parm1, $parm2);
        for ($i = 0; $i < sizeof($parm); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($parm[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                    $pattern .= '|(&#0([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $parm[$i][$j];
            }
            $pattern .= '/i';
            $string = preg_replace($pattern, '', $string);
        }
        return $string;
    }

}
