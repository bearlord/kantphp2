<?php

namespace Kant\Http;

class Json {

    public function output($data) {
        if ($data) {
            $result = json_encode($data, JSON_UNESCAPED_UNICODE);
            return $result;
        }
    }

}
