<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database\Mysql;

use Kant\Exception\KantException;
use PDO;
use Kant\Cache\Cache;

/**
 * Mysql database
 * 
 * @access public
 * @since version 1.1
 * 
 */
class Connection extends \Kant\Database\Connection{

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->connect();
    }

    /**
     *
     * Creates a PDO object and connects to the database.
     *
     * @return void
     */
    public function connect() {    
        if ($this->dbh) {
            return;
        }
        // check for PDO extension
        if (!extension_loaded('pdo')) {
            throw new KantException('The PDO extension is required for this adapter but the extension is not loaded');
        }
        // check for PDO_PGSQL extension
        if (!extension_loaded('pdo_mysql')) {
            throw new KantException('The PDO_MYSQL extension is required for this adapter but the extension is not loaded');
        }
        $dsn = sprintf("mysql:host=%s;dbname=%s", $this->options['hostname'], $this->options['database']);
        //Request a persistent connection, rather than creating a new connection.
        if (isset($this->options['persistent']) && $this->options['persistent'] == true) {
            $extraoptions[PDO::ATTR_PERSISTENT] = true;
        } else {
            $extraoptions = [];
        }
//        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8"; 
        try {
            $this->dbh = new PDO($dsn, $this->options['username'], $this->options['password'], $extraoptions);
            // always use exceptions.
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new KantException(sprintf('Can not connect to MySQL server or cannot use database.%s', $e->getMessage()));
        }
        $this->dbh->exec(sprintf("SET NAMES \"%s\"", $this->options['charset']));
        return $this->dbh;
    }

    /**
     *
     * Close database connection
     *
     */
    public function close() {
        $this->dbh = null;
    }

    /**
     *
     * Execute a query
     *
     * @param string $query
     * @return resource A query result resource on success or false on failure.
     */
    public function execute($sql) {
        if (!is_object($this->dbh)) {
            $this->connect();
        }
        try {
            $sth = $this->dbh->prepare($sql);
            $sth->execute();
        } catch (PDOException $e) {
            throw new KantException(sprintf("MySQL Query Error:%s,Error Code:%s", $sql, $this->dbh->errorCode()));
        }
        $this->numRows = $sth->rowCount();
//        $this->sqls[] = $sql;
//        $this->queryCount++;
//        $this->clearFields();
        return $this->numRows;
    }

    /**
     *
     *  SQl query
     *
     * @param string $sql
     * @param string $method
     * @return array
     */
    public function query($sql, $fetchMode = PDO::FETCH_ASSOC) {
        $rows = null;
        if ($this->ttl) {
            $this->clearFields();
            $rows = Cache::get($sql);
            if (!empty($rows)) {
                return $rows;
            }
        }
        if (!is_resource($this->dbh)) {
            $this->connect();
        }
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $rows = $sth->fetchAll($fetchMode);
        if ($this->ttl) {
            Cache::set($sql, $rows, $this->ttl);
        } else {
            Cache::delete($sql);
        }
        return $rows;
        $this->sqls[] = $sql;
        $this->queryCount++;
        $this->clearFields();
        return $rows;
    }

    /**
     *  Get the ID generated in the last query
     * 
     * @return type
     */
    public function lastInsertId($primaryKey = null) {
        return $this->dbh->lastInsertId();
    }

    /**
     *
     * Get a result row as associative array from SQL query
     *
     * @param method string
     * @param clear_var boolean
     * @return array
     */
    public function fetch($fetchMode = PDO::FETCH_ASSOC) {
        $sql = $this->bluidSql("SELECTE");
        $result = $this->query($sql, $fetchMode);
        return $result;
    }

    /**
     * Fetches the first column of the first row of the SQL result.
     * 
     * @param type $fetchMode
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
        $lastInsertId = $this->lastInsertId($this->primary);
        return $lastInsertId;
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
        return $row->result(0);
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
    
    /**
     * Get Fields
     * 
     * @param type $tableName
     */
    public function getFields($tableName) {
        if (strpos($tableName, '.')) {
            list($dbName, $tableName) = explode('.', $tableName);
            $tableName = $this->getTable($tablename);
            $sql = "SHOW COLUMNS FROM {$dbName}.{$tableName}";
        } else {
            $tableName = $this->getTable($tableName);
            $sql = "SHOW COLUMNS FROM {$tableName}";
        }
        $row = $this->query($sql);
        $info = [];
        if (!empty($row)) {
            foreach ($row as $key => $val) {
                $val = array_change_key_case($val, CASE_LOWER);
                $info[$val['field']] = [
                    'name' => $val['field'],
                    'type' => $val['type'],
                    'notnull' => (bool) ($val['null'] === ''), // not null is empty, null is yes
                    'default' => $val['default'],
                    'primary' => (strtolower($val['key']) == 'pri'),
                    'autoinc' => (strtolower($val['extra']) == 'auto_increment'),
                ];
            }
        }
        return $info;
    }

}
