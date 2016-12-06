<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database;

use Kant\Database\Query;
use Kant\Foundation\Component;

class Connection extends Component {

    protected $options;
    protected $dbh;
    protected $numRows = 0;
    protected $ttl;

    public function __construct($options = []) {
        $this->options = $options;
    }

    
    /**
     * Get query object
     * 
     * @param string $table
     * @param string $model
     * @return object
     */
//    public function getQuery($table, $model) {
//        if (!isset($this->query[$model])) {
//            $this->query[$model] = new Query([
//                'dbh' => $this->dbh,
//                'table' => $this->options['tablepre'] . $table,
//                'model' => $model
//            ]);
//        }
//        return $this->query[$model];
//    }

}
