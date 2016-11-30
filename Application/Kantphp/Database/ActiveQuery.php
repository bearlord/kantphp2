<?php

namespace Kant\Database;

class ActiveQuery extends DbQueryAbstract implements DbQueryInterface {


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
     *
     * Get the ID generated in the last query
     *
     * @return int The ID generated for an AUTO_INCREMENT column by the previous
     * query on success, 0 if the previous
     * query does not generate an AUTO_INCREMENT value, or false if
     * no MySQL connection was established.
     */
    public function lastInsertId($primaryKey = null) {
        
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
        
    }

    
        
    

}
