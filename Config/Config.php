<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Config;

class Config
{

    /**
     * Whether in-memory modifications to configuration data are allowed
     *
     * @var boolean
     */
    protected $_overwrite;

    /**
     * Contains array of configuration data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * KantConfig provides a property based interface to
     * an array.
     *
     * KantConfig also implements Countable and Iterator to
     * facilitate easy access to the data.
     *
     * @param array $array            
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->_data = $data;
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name            
     * @param mixed $default            
     * @return mixed
     */
    public function get($name = null, $default = null, $delimiter = ".")
    {
        if (null === $name) {
            return $this->_data;
        }
        if (false === strpos($name, $delimiter)) {
            return isset($this->_data[$name]) ? $this->_data[$name] : $default;
        }
        $name = explode($delimiter, $name);
        $ret = $this->_data;
        foreach ($name as $key) {
            if (!isset($ret[$key])) {
                return $default;
            }
            $ret = $ret[$key];
        }
        return $ret;
    }

    /**
     * Set objects
     *
     * @param type $name            
     * @param type $value            
     * @param type $delimiter            
     * @throws KantException
     */
    public function set($name, $value = "")
    {
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                $this->_data[$name] = $value;
            } else {
                // 二维数组
                $name = explode(".", $name, 2);
                $this->_data[$name[0]][$name[1]] = $value;
            }

        } elseif (is_array($name)) {
            if (! empty($name)) {
                foreach ($name as $key => $val) {
                    $this->set($key, $value);
                }
            }
        }
    }

    /**
     * Load config file
     *
     * @param type $file            
     * @param type $name            
     * @return type
     */
    public function load($file, $name)
    {
        if (is_file($file)) {
            return self::set(include $file, $name);
        }
    }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function keys()
    {
        return array_keys($this->_data);
    }

    /**
     * Merge object
     *
     * @param type $config            
     * @return \Kant\Config\Config
     */
    public function merge($config)
    {
        $this->_data = $this->_merge($this->_data, $config);
        return $this;
    }

    /**
     * Merge two array
     *
     * @param $arr1
     * @param $arr2
     * @return mixed
     */
    protected function _merge($arr1, $arr2)
    {
        if (is_array($arr2) && ! empty($arr2)) {
            foreach ($arr2 as $key => $value) {
                if (isset($arr1[$key]) && is_array($value)) {
                    $arr1[$key] = $this->_merge($arr1[$key], $arr2[$key]);
                } else {
                    $arr1[$key] = $value;
                }
            }
        }
        return $arr1;
    }

    /**
     * Reference
     *
     * @param type $key            
     * @return type
     */
    public function reference($key = '')
    {
        return $this->_data;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $name            
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Only allow setting of a property if $allowModifications
     * was set to true on construction.
     * Otherwise, throw an exception.
     *
     * @param string $name            
     * @param mixed $value            
     * @throws KantException
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Support isset() overloading on PHP 5.1
     *
     * @param string $name            
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Support unset() overloading on PHP 5.1
     *
     * @param string $name            
     * @throws KantException
     * @return void
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}
