<?php

/**
 * @package KantPHP
 * 
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license BSD License.
 */

namespace Kant\Database\MySQL;

use Kant\Database\DbQueryAbstract;
use Kant\Database\DbQueryInterface;
use Kant\KantException;

!defined('IN_KANT') && exit('Access Denied');

class MysqlDb extends DbQueryAbstract implements DbQueryInterface {

    /**
     *
     * Open database connection
     *
     * @param config
     */
    public function open($config) {
        $this->config = $config;
        if ($config['autoconnect'] == 1) {
            $this->_connect();
        }
    }

    /**
     *
     * Truly open database connection
     *
     * @return resource a MySQL link identifier on success, or false on
     * failure.
     */
    private function _connect() {
        $func = $this->config['persistent'] == 1 ? 'mysql_pconnect' : 'mysql_connect';
        if (!$this->dbh = @$func($this->config['hostname'] . ":" . $this->config['port'], $this->config['username'], $this->config['password'], 1)) {
            throw new KantException(sprintf('Can not connect to MySQL server or cannot use database.%s', mysql_error()));
        }
        if ($this->version() > '4.1') {
            $charset = isset($this->config['charset']) ? $this->config['charset'] : '';
            $serverset = $charset ? "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary" : '';
            $serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',') . " sql_mode='' ") : '';
            $serverset && mysql_query("SET $serverset", $this->dbh);
        }

        if ($this->config['database'] && !@mysql_select_db($this->config['database'], $this->dbh)) {
            throw new KantException(sprintf('Can not use MySQL server or cannot use database.%s', mysql_error()));
        }
        $this->database = $this->config['database'];
    }

    /**
     *
     * Get MySQL server info
     *
     * @return string the MySQL server version on success&return.falseforfailure;.
     */
    public function version() {
        if (!is_resource($this->dbh)) {
            $this->_connect();
        }
        return mysql_get_server_info($this->dbh);
    }

    /**
     * Free result memory
     */
    public function free() {
        if (is_resource($this->queryID)) {
            mysql_free_result($this->queryID);
        }
    }

    /**
     * Close database connection
     */
    public function close() {
        if (is_resource($this->dbh)) {
            mysql_close($this->dbh);
        }
        $this->dbh = null;
    }

    /**
     * Regexp
     * @param type $key
     * @param type $type
     * @param type $value
     * @param type $split
     */
    public function whereRegexp($key, $value, $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        $where = $this->checkField($key) . ' REGEXP ' . $this->quote($value);
        $this->where .= ($this->where ? " $split " : '') . $where;
        return $this;
    }

    /**
     *
     * Execute SQL
     *
     * @param SQL string
     * @return resource
     */
    public function execute($sql) {
        if (!is_resource($this->dbh)) {
            $this->_connect();
        }
        if ($this->queryID) {
            $this->free();
        }
        $this->queryID = mysql_query($sql, $this->dbh);
        if (!$this->queryID) {
            throw new KantException(sprintf("MySQL Query Error:%s,Error Code:%s", $sql, mysql_errno()));
        }
        $this->sqls[] = $sql;
        $this->queryCount++;
        $this->cacheSql();
        return $this->queryID;
    }

    /**
     *  SQl query
     * @param type $sql
     * @param type $method
     * @return boolean
     * @throws KantException
     */
    public function query($sql) {
        $row = null;
        $cacheSqlMd5 = 'sql_' . md5($sql);
        if ($this->ttl) {
            $this->cacheSql();
            $this->clear();
            $rows = $this->cache->get($cacheSqlMd5);
            if (!empty($rows)) {
                return $rows;
            }
        }
        if (!is_resource($this->dbh)) {
            $this->_connect();
        }
        if ($this->queryID) {
            $this->free();
        }
        $this->queryID = mysql_query($sql, $this->dbh);
        if (!$this->queryID) {
            throw new KantException(sprintf("MySQL Query Error:%s,Error Code:%s", $sql, mysql_errno()));
        }
        $this->numRows = mysql_num_rows($this->queryID);
        if ($this->numRows > 0) {
            while ($row = mysql_fetch_array($this->queryID, MYSQL_ASSOC)) {
                $rows[] = $row;
            }
            mysql_data_seek($this->queryID, 0);
        }
        if ($this->ttl) {
            $this->cache->set($cacheSqlMd5, $rows, $this->ttl);
        } else {
            $this->cache->delete($cacheSqlMd5);
        }
        $this->sqls[] = $sql;
        $this->queryCount++;
        $this->cacheSql();
        $this->clear();
        return $rows;
    }

    /**
     *
     * Get the ID generated in the last query
     *
     * @return int The ID generated for an AUTO_INCREMENT column by the previous
     * query on success, 0 if the previous
     * query does not generate an AUTO_INCREMENT value, or false if
     * no MySQL connection was established.
     */
    public function lastInsertId($primaryKey = null) {
        return ($id = mysql_insert_id($this->dbh)) >= 0 ? $id : mysql_result($this->query("SELECT last_insert_id()"), 0);
    }

    /**
     *
     * Fetch a result row as an associative array
     *
     * @param resource resource
     * @return array an associative array of strings that corresponds to the fetched row, or
     * false if there are no more rows.
     */
    public function fetch($fetchMode = '') {
        $sql = $this->bluidSql("SELECT");
        $result = $this->query($sql);
        return $result;
    }

    /**
     * Fetches the first column of the first row of the SQL result.
     * 
     */
    public function fetchOne() {
        $this->limit = 1;
        $result = $this->fetch();
        if ($result) {
            return $result[0];
        }
    }

    /**
     * 
     *  Get a result row as associative array from SQL query with easy method
     * 
     * @param string $select
     * @param string $from
     * @param string $where
     * @param string $groupby
     * @param string $orderby
     * @param string $limit
     * @return array
     */
    public function fetchEasy($select, $from, $where = null, $groupby = null, $orderby = null, $limit = null) {
        $this->select($select);
        $this->from($from);
        if ($where) {
            foreach ($where as $sk => $sv) {
                $this->where($sk, $sv);
            }
        }
        if ($groupby) {
            $this->groupby($groupby);
        }
        if ($orderby) {
            $this->orderby($orderby[0], $orderby[1]);
        }
        if ($limit) {
            $this->limit($limit[0], $limit[1]);
        }
        return $this->fetch();
    }

    /**
     *  Insert Data
     * 
     * @return
     */
    public function insert() {
        $sql = $this->bluidSql("INSERT");
        $this->execute($sql);
        return $this->lastInsertId();
    }

    /**
     * Update Data
     * 
     * @return 
     */
    public function update() {
        $sql = $this->bluidSql("UPDATE");
        $result = $this->execute($sql);
        return $result;
    }

    /**
     * Delete Data
     * 
     * @return
     */
    public function delete() {
        $sql = $this->bluidSql("DELETE");
        $result = $this->execute($sql);
        return $result;
    }

    /**
     *
     * Get the number of rows in a result
     *
     * @param clear_var boolean
     * @return integer The number of rows in a result set on success&return.falseforfailure;.
     */
    public function count() {
        $sql = $this->bluidSql("SELECT", true);
        $row = $this->query($sql);
        return $row[0]['count'];
    }

    /**
     * 
     * Start transaction
     */
    public function begin() {
        $this->execute('SET AUTOCOMMIT=0');
        $this->execute('BEGIN');
    }

    /**
     * 
     * Commit
     */
    public function commit() {
        $this->execute('COMMIT');
    }

    /**
     * 
     * Rollback
     */
    public function rollback() {
        $this->execute('ROLLBACK');
    }

    /**
     * Clone table structure and indexes
     * 
     * @param string $table
     * @param string $newTable
     * @return
     */
    public function cloneTable($table, $newTable) {
        $sql = "CREATE TABLE  $newTable(LIKE $table)";
        $result = $this->execute($sql);
        return $result;
    }

    /**
     * Determine whether table exists
     * 
     * @param string $table
     * @return boolean
     */
    public function tableExists($table) {
        $tables = $this->listTables();
        return in_array($table, $tables) ? true : false;
    }

    /**
     * List tables
     * 
     * @return
     */
    public function listTables() {
        $tables = array();
        $row = $this->query("SHOW TABLES");
        if (!empty($row)) {
            foreach ($row as $val) {
                $val = array_values($val);
                $tables[] = $val[0];
            }
        }
        return $tables;
    }

}
