<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Hook;

!defined('IN_KANT') && exit('Access Denied');

class Hook {

    static private $tags = array();

    /**
     * Dynamically added plugin to a tag
     * 
     * @param string $tag tag name
     * @param mixed $name plugin name
     * @return void
     */
    static public function add($tag, $name) {
        if (!isset(self::$tags[$tag])) {
            self::$tags[$tag] = array();
        }
        if (is_array($name)) {
            self::$tags[$tag] = array_merge(self::$tags[$tag], $name);
        } else {
            self::$tags[$tag][] = $name;
        }
    }

    /**
     * Import plugins
     * 
     * @param array $data plugin information
     * @param boolean $recursive recursive 
     * @return void
     */
    static public function import($data, $recursive = true) {
        if (!$recursive) {
            self::$tags = array_merge(self::$tags, $data);
        } else {
            foreach ($data as $tag => $val) {
                if (!isset(self::$tags[$tag]))
                    self::$tags[$tag] = array();
                if (!empty($val['_overlay'])) {
                    // specify the overwrite mode for a tag
                    unset($val['_overlay']);
                    self::$tags[$tag] = $val;
                } else {
                    // merge mode
                    self::$tags[$tag] = array_merge(self::$tags[$tag], $val);
                }
            }
        }
    }

    /**
     * Get the tag information
     * @param string $tag
     * @return array
     */
    static public function get($tag = '') {
        if (empty($tag)) {
            // get all of the plug-in information
            return self::$tags;
        } else {
            return self::$tags[$tag];
        }
    }

    /**
     * Listion tag's plugin
     * 
     * @param string $tag
     * @param mixed $params
     * @return void
     */
    static public function listen($tag, &$params = NULL) {
        if (isset(self::$tags[$tag])) {
            foreach (self::$tags[$tag] as $name) {
                $result = self::exec($name, $tag, $params);
                if (false === $result) {
                    return;
                }
            }
        }
        return;
    }

    /**
     * Exec plugin
     * 
     * @param string $name plugin name
     * @param string $tag tag name     
     * @param Mixed $params params
     * @return void
     */
    static public function exec($name, $tag, &$params = NULL) {
        if ('Component' == substr($name, -9)) {
            $tag = 'run';
        }
        $filepath = KANT_PATH . "Component/" . $name . ".php";
        if (file_exists($filepath)) {
            require_once $filepath;
        }
        $addon = new $name();
        return $addon->$tag($params);
    }

}
