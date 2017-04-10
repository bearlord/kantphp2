<?php

namespace App\Index\Model;

use Kant\Database\ActiveRecord;

class Posts extends ActiveRecord {

    public $verifyCode;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%posts}}';
    }

}
