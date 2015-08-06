<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
!defined('IN_KANT') && exit('Access Denied');

namespace Database\PDO;

use Kant\Database\DbQueryAbstract;
use Kant\Database\DbQueryInterface;
use Kant\KantException;
//use PDO;

/**
 * Postgresql database
 * 
 * @access public
 * @since version 1.1
 * 
 */
class PdoPgsqlDb extends DbQueryAbstract implements DbQueryInterface {

    public function __construct() {
        parent::__construct();
    }

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
     * Creates a PDO object and connects to the database.
     *
     * @return void
     */
    private function _connect() {
        if ($this->dbh) {
            return;
        }
        // check for PDO extension
        if (!extension_loaded('pdo')) {
            throw new KantException('The PDO extension is required for this adapter but the extension is not loaded');
        }
        // check for PDO_PGSQL extension
        if (!extension_loaded('pdo_pgsql')) {
            throw new KantException('The PDO_PGSQL extension is required for this adapter but the extension is not loaded');
        }

        $dsn = sprintf("%s:host=%s;dbname=%s", $this->config['type'], $this->config['hostname'], $this->config['database']);

        //Request a persistent connection, rather than creating a new connection.
        if (isset($this->config['persistent']) && $this->config['persistent'] == true) {
            $options = array(PDO::ATTR_PERSISTENT => true);
        } else {
            $options = null;
        }
        try {
            $this->dbh = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            // always use exceptions.
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new KantException(sprintf('Can not connect to PostgreSQL server or cannot use database.%s', $e->getMessage()));
        }
        $this->dbh->exec(sprintf("SET NAMES \"%s\"", $this->config['charset']));
        $this->database = $this->config['database'];
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
            $this->_connect();
        }

        $query = $this->dbh->exec($sql);
        if (!$query) {
            throw new KantException(sprintf('PostgreSQL Query Error:%s,Error Code:%s', $sql, $this->dbh->errorCode()));
        }
        $this->sqls[] = $sql;
        $this->queryCount++;
        return $query;
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
        $cacheSqlMd5 = 'sql_' . md5($sql);
        if ($this->ttl) {
            $rows = $this->cache->get($cacheSqlMd5);
            if (!empty($rows)) {
                return $rows;
            }
        }
        if (!is_resource($this->dbh)) {
            $this->_connect();
        }
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $rows = $sth->fetchAll($fetchMode);
        if ($this->ttl) {
            $this->cache->set($cacheSqlMd5, $rows, $this->ttl);
        } else {
            $this->cache->delete($cacheSqlMd5);
        }
        $this->sqls[] = $sql;
        $this->queryCount++;
        return $rows;
    }

    /**
     *  Get the ID generated in the last query
     * 
     * @return type
     */
    public function lastInsertId($primaryKey = null) {
        $sequenceName = $this->getTable($this->table);
        if ($primaryKey) {
            $sequenceName .= "_$primaryKey";
        }
        $sequenceName .= '_seq';
        return $this->dbh->lastInsertId($sequenceName);
    }

    /**
     *
     * Get a result row as associative array from SQL query
     *
     * @param method string
     * @param clear_var boolean
     * @return array
     */
    public function fetch($fetchMode = PDO::FETCH_ASSOC, $clearVar = true) {
        $sql = $this->getSql(0);
        $result = $this->query($sql, $fetchMode);
        $this->cacheSql();
        if ($clearVar) {
            $this->clear();
        }
        return $result;
    }

    /**
     * Fetches the first column of the first row of the SQL result.
     * 
     * @param type $fetchMode
     * @param type $clearVar
     */
    public function fetchOne($clearVar = true) {
        if ($this->from) {
            $this->limit = 1;
        }
        $sql = $this->getSql(0);
        if (!is_resource($this->dbh)) {
            $this->_connect();
        }
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        $this->sqls[] = $sql;
        $this->queryCount++;
        $this->cacheSql();
        if ($clearVar) {
            $this->clear();
        }
        return $result;
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
     * @param boolean $replace
     * @param boolean $clearVar
     * @return
     */
    public function insert($replace = false, $clearVar = true) {
        $sql = $this->insertSql($replace, 0);
        $this->execute($sql);
        $this->cacheSql();
        $lastInsertId = $this->lastInsertId($this->primary);
        if ($clearVar) {
            $this->clear();
        }
        return $lastInsertId;
    }

    /**
     * Update Data
     * 
     * @param boolean $clearVar
     * @return 
     */
    public function update($clearVar = true) {
        $sql = $this->insertSql(true, true);
        $result = $this->execute($sql, 'unbuffer');
        $this->_cacheSql();
        if ($clearVar) {
            $this->clear();
        }
        return $result;
    }

    /**
     * Delete Data
     * 
     * @param boolean $clearVar
     * @return
     */
    public function delete($clearVar = true) {
        $sql = $this->deleteSql();
        $result = $this->execute($sql);
        $this->cacheSql();
        if ($clearVar) {
            $this->clear();
        }
        return $result;
    }

    /**
     *
     * Get the number of rows in a result
     *
     * @param clear_var boolean
     * @return integer The number of rows in a result set on success&return.falseforfailure;.
     */
    public function count($clearVar = true) {
        $sql = $this->getSql(1);
        $row = $this->query($sql);
        $this->cacheSql();
        if ($clearVar) {
            $this->clear();
        }
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
        $sql = "CREATE TABLE  $newTable(LIKE $table INCLUDING INDEXES)";
        $result = $this->execute($sql);
        return $result;
    }

}
