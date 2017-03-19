<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cache\Driver;

/**
 * File Cache
 * 
 * @access public
 * @since version 1.1
 */
class File extends \Kant\Cache\Cache {

    protected $options = [
        'expire' => 0,
        'cache_subdir' => false,
        'prefix' => '',
        'path' => RUNTIME_PATH . 'Cache/',
        'data_compress' => false,
        'lock_ex' => false
    ];

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
    public function __construct($options = '') {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (substr($this->options['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->options['path'] .= DIRECTORY_SEPARATOR;
        }
        $this->init();
    }

    private function init() {
        if (!is_dir($this->options['path'])) {
            if (mkdir($this->options['path'], 0755, true)) {
                return true;
            }
        }
        return false;
    }

    public function setExpire($expire) {
        if (!empty($expire)) {
            $this->options['expire'] = $expire;
        }
    }

    protected function getCacheKey($name) {
        $name = $this->buildKey($name);
        if ($this->options['cache_subdir']) {
            // 使用子目录
            $name = substr($name, 0, 2) . DIRECTORY_SEPARATOR . substr($name, 2);
        }
        if ($this->options['prefix']) {
            $name = $this->options['prefix'] . DIRECTORY_SEPARATOR . $name;
        }
        $filename = $this->options['path'] . $name . '.php';
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $filename;
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
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $filename = $this->getCacheKey($name);
        $data = serialize($value);
        if ($this->options['data_compress'] && function_exists('gzcompress')) {
            $data = gzcompress($data, 3);
        }
        $data = "<?php\n//" . sprintf('%012d', $expire) . $data . "\n?>";
        if ($this->options['lock_ex']) {
            $result = file_put_contents($filename, $data, LOCK_EX);
        } else {
            $result = file_put_contents($filename, $data);
        }
        return $result ? $result : 'false';
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
    public function get($name, $default = false) {
        $filename = $this->getCacheKey($name);
        if (!is_file($filename)) {
            return $default;
        }
        $content = file_get_contents($filename);
        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);
            if (0 != $expire && $_SERVER['REQUEST_TIME'] > filemtime($filename) + $expire) {
                $this->unlink($filename);
                return $default;
            }
            $content = substr($content, 20, -3);
            if ($this->options['data_compress'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        } else {
            return $default;
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
        return $this->unlink($this->getCacheKey($name));
    }

    /**
     * Flush cache
     * 
     * @return boolean
     */
    public function flush() {
        $files = (array) glob($this->options['path'] . ($this->options['prefix'] ? $this->options['prefix'] . DIRECTORY_SEPARATOR : '') . '*');
        foreach ($files as $path) {
            if (is_dir($path)) {
                array_map('unlink', glob($path . '/*.php'));
            } else {
                unlink($path);
            }
        }
        return true;
    }

    private function unlink($path) {
        return is_file($path) && unlink($path);
    }

}

?>