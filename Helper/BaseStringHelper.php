<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Helper;

/**
 * BaseStringHelper provides concrete implementation for [[StringHelper]].
 *
 * Do not use BaseStringHelper. Use [[StringHelper]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseStringHelper
{

    /**
     * Returns the number of bytes in the given string.
     * This method ensures the string is treated as a byte array by using `mb_strlen()`.
     * 
     * @param string $string
     *            the string being measured for length
     * @return integer the number of bytes in the given string.
     */
    public static function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }

    /**
     * Returns the portion of string specified by the start and length parameters.
     * This method ensures the string is treated as a byte array by using `mb_substr()`.
     * 
     * @param string $string
     *            the input string. Must be one character or longer.
     * @param integer $start
     *            the starting position
     * @param integer $length
     *            the desired portion length. If not specified or `null`, there will be
     *            no limit on length i.e. the output will be until the end of the string.
     * @return string the extracted part of string, or FALSE on failure or an empty string.
     * @see http://www.php.net/manual/en/function.substr.php
     */
    public static function byteSubstr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length === null ? mb_strlen($string, '8bit') : $length, '8bit');
    }

    /**
     * Returns the trailing name component of a path.
     * This method is similar to the php function `basename()` except that it will
     * treat both \ and / as directory separators, independent of the operating system.
     * This method was mainly created to work on php namespaces. When working with real
     * file paths, php's `basename()` should work fine for you.
     * Note: this method is not aware of the actual filesystem, or path components such as "..".
     *
     * @param string $path
     *            A path string.
     * @param string $suffix
     *            If the name component ends in suffix this will also be cut off.
     * @return string the trailing name component of the given path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename($path, $suffix = '')
    {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, - $len) === $suffix) {
            $path = mb_substr($path, 0, - $len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }
        
        return $path;
    }

    /**
     * Returns parent directory's path.
     * This method is similar to `dirname()` except that it will treat
     * both \ and / as directory separators, independent of the operating system.
     *
     * @param string $path
     *            A path string.
     * @return string the parent directory's path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function dirname($path)
    {
        $pos = mb_strrpos(str_replace('\\', '/', $path), '/');
        if ($pos !== false) {
            return mb_substr($path, 0, $pos);
        } else {
            return '';
        }
    }

    /**
     * Truncates a string to the number of words specified.
     *
     * @param string $string
     *            The string to truncate.
     * @param integer $count
     *            How many words from original string to include into truncated string.
     * @param string $suffix
     *            String to append to the end of truncated string.
     * @param boolean $asHtml
     *            Whether to treat the string being truncated as HTML and preserve proper HTML tags.
     *            This parameter is available since version 2.0.1.
     * @return string the truncated string.
     */
    public static function truncateWords($string, $count, $suffix = '...', $asHtml = false)
    {
        if ($asHtml) {
            return static::truncateHtml($string, $count, $suffix);
        }
        
        $words = preg_split('/(\s+)/u', trim($string), null, PREG_SPLIT_DELIM_CAPTURE);
        if (count($words) / 2 > $count) {
            return implode('', array_slice($words, 0, ($count * 2) - 1)) . $suffix;
        } else {
            return $string;
        }
    }

    /**
     * Explodes string into array, optionally trims values and skips empty ones
     *
     * @param string $string
     *            String to be exploded.
     * @param string $delimiter
     *            Delimiter. Default is ','.
     * @param mixed $trim
     *            Whether to trim each element. Can be:
     *            - boolean - to trim normally;
     *            - string - custom characters to trim. Will be passed as a second argument to `trim()` function.
     *            - callable - will be called for each value instead of trim. Takes the only argument - value.
     * @param boolean $skipEmpty
     *            Whether to skip empty strings between delimiters. Default is false.
     * @return array
     * @since 2.0.4
     */
    public static function explode($string, $delimiter = ',', $trim = true, $skipEmpty = false)
    {
        $result = explode($delimiter, $string);
        if ($trim) {
            if ($trim === true) {
                $trim = 'trim';
            } elseif (!is_callable($trim)) {
                $trim = function ($v) use($trim) {
                    return trim($v, $trim);
                };
            }
            $result = array_map($trim, $result);
        }
        if ($skipEmpty) {
            // Wrapped with array_values to make array keys sequential after empty values removing
            $result = array_values(array_filter($result, function ($value) {
                return $value !== '';
            }));
        }
        return $result;
    }

    /**
     * Counts words in a string
     * 
     * @since 2.0.8
     *       
     * @param string $string            
     * @return integer
     */
    public static function countWords($string)
    {
        return count(preg_split('/\s+/u', $string, null, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param type $value            
     * @return type
     */
    public static function studly($value)
    {
        $value = ucwords(str_replace([
            '-',
            '_'
        ], ' ', $value));
        $value = str_replace(' ', '', $value);
        return $value;
    }
}
