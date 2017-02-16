<?php

namespace Kant\Session\Driver\Mysql;

class Model extends \Kant\Database\ActiveRecord {

    public static function tableName() {
        return "{{%session}}";
    }
}

?>
