<?php
/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Filemanager;


use Kant\Helper\Url;

class Utils
{

    /**
     * Convert convert size in bytes to human readable
     *
     * @param  int $size
     *
     * @return  string
     */
    public static function makeSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $u = 0;
        while ((round($size / 1024) > 0) && ($u < 4)) {
            $size = $size / 1024;
            $u++;
        }

        return (number_format($size, 0) . " " . $units[$u]);
    }

}