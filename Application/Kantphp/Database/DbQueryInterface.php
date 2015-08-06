<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database;

/**
 * Database query interface class
 * 
 * @access public
 * @abstract
 * @since version 1.1
 */
interface DbQueryInterface {

    public function fetch($fetchMode = PDO::FETCH_ASSOC, $clearVar = true);

    public function fetchEasy($select, $from, $where = null, $groupby = null, $orderby = null, $limit = null);

    public function fetchOne($clearVar = true);

    public function insert($replace = false, $clearVar = true);

    public function lastInsertId($primaryKey = null);

    public function update($clearVar = true);

    public function delete($clearVar = true);

    public function count($clearVar = true);

    public function begin();

    public function commit();

    public function rollback();

    public function cloneTable($table, $newTable);
}

?>
