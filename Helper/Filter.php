<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Helper;

/**
 * Filter class
 *
 * @access public
 * @version 1.1
 * @since version 1.1
 */
class Filter
{

    /**
     * Quote string with slashes
     *
     * @param type $string            
     * @return type
     */
    public static function addslashes($string)
    {
        if (!is_array($string)) {
            return addslashes($string);
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = addslashes($val);
            }
            return $string;
        }
    }

    /**
     *
     * Convert special characters to HTML entities
     *
     * @param
     *            string string,array
     * @return string,array The converted string,array.
     */
    public static function htmlspecialchars($string)
    {
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
    public static function intval($number)
    {
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
     * @param
     *            string string,array;
     * @return string,array The converted string,array.
     */
    public static function textval($string)
    {
        if (!is_array($string)) {
            $string = is_string($string) ? self::xss(htmlspecialchars(trim($string), ENT_QUOTES)) : $string;
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = self::textval($val);
            }
        }
        return str_replace($wu, $rp, $string);
    }

    /**
     *
     * Format HTML for security reasons
     *
     * @param
     *            string string
     * @return string string
     */
    public static function html($string)
    {
        if (!is_array($string)) {
            $string = is_string($string) ? self::xss($string) : $string;
        } else {
            foreach ($string as $key => $val) {
                $string[$key] = self::html($val);
            }
        }
        return $string;
    }

    /**
     * Un-quotes a quoted string
     *
     * @param type $string            
     * @return type
     */
    public static function stripslashes($string)
    {
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
     * 安全过滤函数
     *
     * @param
     *            $string
     * @return string
     */
    public static function safereplace($string)
    {
        $string = str_replace('%20', '', $string);
        $string = str_replace('%27', '', $string);
        $string = str_replace('%2527', '', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace("{", '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('\\', '', $string);
        $string = self::xss($string);
        return $string;
    }

    /**
     * xss过滤函数
     *
     * @param
     *            $string
     * @return string
     */
    public static function xss($string)
    {
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
        $parm1 = Array(
            'javascript',
            'vbscript',
            'expression',
            'applet',
            'meta',
            'xml',
            'blink',
            'link',
            'script',
            'embed',
            'object',
            'iframe',
            'frame',
            'frameset',
            'ilayer',
            'layer',
            'bgsound',
            'title',
            'base'
        );
        $parm2 = Array(
            'onabort',
            'onactivate',
            'onafterprint',
            'onafterupdate',
            'onbeforeactivate',
            'onbeforecopy',
            'onbeforecut',
            'onbeforedeactivate',
            'onbeforeeditfocus',
            'onbeforepaste',
            'onbeforeprint',
            'onbeforeunload',
            'onbeforeupdate',
            'onblur',
            'onbounce',
            'oncellchange',
            'onchange',
            'onclick',
            'oncontextmenu',
            'oncontrolselect',
            'oncopy',
            'oncut',
            'ondataavailable',
            'ondatasetchanged',
            'ondatasetcomplete',
            'ondblclick',
            'ondeactivate',
            'ondrag',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'onerror',
            'onerrorupdate',
            'onfilterchange',
            'onfinish',
            'onfocus',
            'onfocusin',
            'onfocusout',
            'onhelp',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onlayoutcomplete',
            'onload',
            'onlosecapture',
            'onmousedown',
            'onmouseenter',
            'onmouseleave',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onmousewheel',
            'onmove',
            'onmoveend',
            'onmovestart',
            'onpaste',
            'onpropertychange',
            'onreadystatechange',
            'onreset',
            'onresize',
            'onresizeend',
            'onresizestart',
            'onrowenter',
            'onrowexit',
            'onrowsdelete',
            'onrowsinserted',
            'onscroll',
            'onselect',
            'onselectionchange',
            'onselectstart',
            'onstart',
            'onstop',
            'onsubmit',
            'onunload'
        );
        $parm = array_merge($parm1, $parm2);
        for ($i = 0; $i < sizeof($parm); $i ++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($parm[$i]); $j ++) {
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
