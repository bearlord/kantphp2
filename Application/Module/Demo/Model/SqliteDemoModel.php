<?php

namespace Demo\Model;

use Kant\Model\BaseModel;

class SqliteDemoModel extends BaseModel {

    protected $adapter = 'sqlite';
    protected $table = 'user';
    protected $primary = 'id';

//    public function __construct() {
//        parent::__construct();
//    }

}

?>
