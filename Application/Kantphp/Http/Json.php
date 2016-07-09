<?php

namespace Kant\Http;

class Json {

    public function output($data) {
        $result = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $result;
    }

}
