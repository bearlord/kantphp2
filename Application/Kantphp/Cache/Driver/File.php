<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache\Driver;

/**
 * File Cache
 * 
 * @access public
 * @since version 1.1
 */
class File {

    /**
     * Cache default config
     *
     */
    protected $_setting = array(
        /* Cache file suffix */
        'suf' => '.php',
        /* Cache format：array，serialize，null(string) */
        'type' => 'array',
        'lock_ex' => true
    );

    /**
     * Cache path
     *
     */
    protected $filepath = '';

    /**
     *
     * Construct
     *
     * @param setting array
     */
    public function __construct($setting = '') {
        $this->getSetting($setting);
        $this->filepath = CACHE_PATH . 'CacheData/';
    }

    /**
     *
     * Set cache
     *
     * @param name string
     * @param data 	mixed
     * @param setting array
     * @param type 	string
     * @return  mixed on success or false
     */
    public function set($name, $data, $expire = 0) {
        $filename = $this->filepath . $name . $this->_setting['suf'];
        if (!is_dir($this->filepath)) {
            mkdir($this->filepath, 0777, true);
        }
        if ($this->_setting['type'] == 'array') {
            $data = "<?php\nreturn " . var_export($data, true) . ";\n?>";
        } elseif ($this->_setting['type'] == 'serialize') {
            $data = serialize($data);
        }
        if ($this->_setting['lock_ex']) {
            $file_size = file_put_contents($filename, $data, LOCK_EX) && $this->setExpire($name, $expire);
        } else {
            $file_size = file_put_contents($filename, $data) && $this->setExpire($name, $expire);
        }
        return $file_size ? $file_size : 'false';
    }

    public function setExpire($name, $expire = 0) {
        $filename = $this->filepath . $name . '_expire' . $this->_setting['suf'];
        $expire = ($expire == 0) ? $expire : (time() + $expire);
        $data = "<?php\nreturn " . var_export($expire, true) . ";\n?>";
        $file_size = file_put_contents($filename, $data, LOCK_EX);
        return $file_size ? $file_size : 'false';
    }

    /**
     *
     * Get cache
     *
     * @param name string
     * @param setting array
     * @param type string
     * @return mixed
     */
    public function get($name) {
        if (($this->getExpire($name) > 0) && $this->getExpire($name) < time()) {
            $this->delete($name) && $this->deleteExpire($name);
            return;
        }
        $filename = $this->filepath . $name . $this->_setting['suf'];
        if (!file_exists($filename)) {
            return false;
        } else {
            if ($this->_setting['type'] == 'array') {
                $data = @require($filename);
            } elseif ($this->_setting['type'] == 'serialize') {
                $data = unserialize(file_get_contents($filename));
            }
            return $data;
        }
    }

    public function getExpire($name) {
        $filename = $this->filepath . $name . '_expire' . $this->_setting['suf'];
        if (!file_exists($filename)) {
            return false;
        } else {
            $data = @require($filename);
            return $data;
        }
    }

    /**
     *
     * Delete cache
     *
     * @param name string
     * @param setting array
     * @param type string
     * @return bool
     */
    public function delete($name) {
        $this->deleteExpire($name);
        $filename = $this->filepath . $name . $this->_setting['suf'];
        if (file_exists($filename)) {
            return @unlink($filename) ? true : false;
        } else {
            return false;
        }
    }

    public function deleteExpire($name) {
        $filename = $this->filepath . $name . '_expire' . $this->_setting['suf'];
        if (file_exists($filename)) {
            return @unlink($filename) ? true : false;
        } else {
            return false;
        }
    }

    /**
     *
     * Get user defined setting
     *
     * @param setting array
     */
    private function getSetting($setting = '') {
        if ($setting) {
            $this->_setting = array_merge($this->_setting, $setting);
        }
    }

    /**
     *
     * Cache info
     *
     * @param name string
     * @param setting string
     * @param type string
     */
    public function cacheInfo($name) {
        $filename = $this->filepath . $name . $this->_setting['suf'];
        if (file_exists($filename)) {
            $res['filename'] = $name . $this->_setting['suf'];
            $res['filepath'] = $this->filepath;
            $res['filectime'] = filectime($filename);
            $res['filemtime'] = filemtime($filename);
            $res['filesize'] = filesize($filename);
            return $res;
        } else {
            return false;
        }
    }

}

/**
 * 2013-05-23 modified
 */
?>