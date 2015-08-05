<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Log;

!defined('IN_KANT') && exit('Access Denied');

/**
 * 日志处理类
 */
class Log {

    const EMERG = 'EMERG';
    const ALERT = 'ALERT';
    const CRIT = 'CRIT';
    const ERR = 'ERR';
    const WARN = 'WARN';
    const NOTICE = 'NOTIC';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    const SQL = 'SQL';

    // 日志信息

    static protected $log = array();
    // 日志存储
    static protected $storage = null;

    // 日志初始化
    static public function init($config = array()) {
        $type = isset($config['type']) ? $config['type'] : 'File';
        $class = "Log" . ucwords(strtolower($type));
        unset($config['type']);
        require $class . '.php';
        $className = "\\Log\\" . $class;
        self::$storage = new $className($config);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @return void
     */
    static function record($message, $level = self::ERR, $record = false) {
        if ($record || false !== strpos(C('LOG_LEVEL'), $level)) {
            self::$log[] = "{$level}: {$message}\r\n";
        }
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function save($type = '', $destination = '') {
        if (empty(self::$log)) {
            return;
        }
        if (empty($destination)) {
            $destination = LOG_PATH . date('y_m_d') . '.log';
        }
        if (!self::$storage) {
            return;
        }
        $message = implode('', self::$log);
        self::$storage->write($message, $destination);
        // 保存后清空日志缓存
        self::$log = array();
    }

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function write($message, $level = self::ERR, $type = '', $destination = '') {
        if (!self::$storage) {
            return;
        }
        if (empty($destination)) {
            $destination = LOG_PATH . date('y_m_d') . '.log';
        }
        self::$storage->write("{$level}: {$message}", $destination);
    }

}
