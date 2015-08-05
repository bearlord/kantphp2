<?php

/**
 * Mysql Demo 模型
 */
class MysqlDemoModel extends BaseModel {

    protected $table = 'user';
    protected $primary = 'id';

    public function __construct() {
        parent::__construct();
    }

    public function getUserByid($id) {
        $row = $this->db->from($this->table)->where('id', $id)->fetch();
        //如果调试，显示SQL语句，可以打印如下语句
        //var_dump($this->db->sqls);
        return $row;
    }

}

?>
