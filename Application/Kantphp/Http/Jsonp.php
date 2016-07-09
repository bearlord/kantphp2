<?php

namespace Kant\Http;

class Jsonp {

    protected $options = [
        'var_jsonp_handler' => 'callback',
        'default_jsonp_handler' => 'jsonpReturn'
    ];

    protected function output($data) {
        $handler = !empty($_GET[$this->options['var_jsonp_handler']]) ? $_GET[$this->options['var_jsonp_handler']] : $this->options['default_jsonp_handler'];
        $result = $handler . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');';
        return $result;
    }

}
