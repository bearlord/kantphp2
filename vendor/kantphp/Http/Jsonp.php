<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Http;

class Jsonp {

    protected $options = [
        'var_jsonp_handler' => 'callback',
        'default_jsonp_handler' => 'jsonpReturn'
    ];

    public function output($data) {
        $handler = !empty($_GET[$this->options['var_jsonp_handler']]) ? $_GET[$this->options['var_jsonp_handler']] : $this->options['default_jsonp_handler'];
        $result = $handler . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');';
        return $result;
    }

}
