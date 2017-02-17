<?php

namespace App\Index\Model;

class Posts extends \Kant\Database\ActiveRecord {
    
    protected $table = "{{%posts}}";
    
    public function rules() {
        return [
            [['p_title'], 'required'],
            [['p_content'], 'required', 'message'=> '内容不能为空'],
        ];
    }
    
    public function attributeLabels() {
        return [
            'p_title' => '标题'
        ];
    }
}
