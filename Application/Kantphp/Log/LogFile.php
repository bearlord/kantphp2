<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Log;

!defined('IN_KANT') && exit('Access Denied');

class LogFile {

    protected $config = array(
        'log_time_format' => ' c ',
        'log_file_size' => 2097152,
        'log_path' => '',
    );

    // 实例化并传入参数
    public function __construct($config = array()) {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public function write($log, $destination = '') {
        $now = date($this->config['log_time_format']);
        if (empty($destination)) {
            $destination = $this->config['log_path'] . date('y_m_d') . '.log';
        }
        if (!is_dir($this->config['log_path'])) {
            mkdir($this->config['log_path'], 0755, true);
        }
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . '/' . time() . '-' . basename($destination));
        }
        $ip = get_client_ip();
        $request_url = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI MODE';
        error_log("[{$now}] " . $ip . ' ' . $request_url . "\r\n{$log}\r\n", 3, $destination);
    }

}
