<?php

class CategoryModel extends BaseModel {

    protected $table = 'category';
    protected $primary = 'category_id';

    public function getinfo() {
        $row = $this->db->from($this->table)->fetch();
        var_dump($row);
    }

}