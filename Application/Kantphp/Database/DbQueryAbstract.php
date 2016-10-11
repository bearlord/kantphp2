<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database;

use Kant\Base;
use Kant\Exception\KantException;

!defined('IN_KANT') && exit('Access Denied');

/**
 * Database query abstract class
 * 
 * @access public
 * @abstract
 * @since version 1.1
 */
abstract class DbQueryAbstract extends Base {

    //Connection identifier
    protected $dbh = '';
    protected $config;
    protected $queryID;
    protected $numRows;
    public $dbTablepre = '';
    protected $table = '';
    protected $where = '';
    protected $set = '';
    protected $select = '';
    protected $from = '';
    protected $groupby = '';
    protected $orderby = '';
    protected $limit = '';
    protected $ttl = 0;
    protected $varFields = array('set', 'select', 'from', 'where', 'groupby', 'orderby', 'limit', 'ttl');
    protected $sqls = array();
    protected $queryCount;

    /**
     *
     * Get a table
     *
     * @param tablename string
     */
    public function getTable($tablename) {
        return $this->dbTablepre . $tablename;
    }

    /**
     *
     * Set from in a SQL
     *
     * @param tablename string
     * @param asname string
     */
    public function from($tablename, $asname = null) {
        $this->from = $this->getTable($tablename) . ($asname ? " $asname" : '');
        return $this;
    }

    /**
     * Join table
     * 
     * @param string/array $join
     */
    public function join($join) {
        $joinStr = '';
        if (!empty($join)) {
            if (is_array($join)) {
                foreach ($join as $key => $_join) {
                    if (false !== stripos(strtoupper($_join), 'JOIN')) {
                        $joinStr .= ' ' . $_join;
                    } else {
                        $joinStr .= ' LEFT JOIN ' . $_join;
                    }
                }
            } else {
                $joinStr .= ' LEFT JOIN ' . $join;
            }
        }
        $this->from .= $joinStr;
        return $this;
    }

    /**
     *
     * Set query field
     *
     * @param fields string
     * @param asname string
     * @param fun string
     */
    public function select($fields, $asname = null, $fun = null) {
        $split = $this->select ? ',' : '';
        if (is_string($fields)) {
            if ($fun) {
                $fields = str_replace('?', $this->checkField($fields), $fun);
            } else {
                $fields = $this->checkField($fields);
            }
            $select = $fields . ($asname ? (' AS ' . $this->checkField($asname)) : '');
            $this->select .= $split . $select;
        } elseif (is_array($fields)) {
            foreach ($fields as $key) {
                $this->select .= $split . $this->checkField($fields);
                $split = ',';
            }
        }
        return $this;
    }

    /**
     *
     * Set the field's value
     *
     * @param key string
     * @param value string
     */
    public function set($key, $value = null) {
        if (empty($key)) {
            return $this;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $this->checkField($key);
            $this->set[$key] = $this->quote($value);
        }
        return $this;
    }

    /**
     *
     * Set the field's value cumulative
     *
     * @param key string
     * @param value string
     */
    public function setAdd($key, $value = 1) {
        if (empty($key)) {
            return $this;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setAdd($k, $v);
            }
        } else {
            $this->checkField($key);
            $this->set[$key] = $key . '+' . $this->quote($value);
        }
        return $this;
    }

    /**
     *
     * Set the field's value regressive
     *
     * @param key string
     * @param value string
     */
    public function setDec($key, $value = 1) {
        if (empty($key)) {
            return $this;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setAdd($k, $v);
            }
        } else {
            $this->checkField($key);
            $this->set[$key] = $key . '-' . $this->quote($value);
        }
        return $this;
    }

    /**
     *
     * Set query clause WHERE field = value
     *
     * @param key string
     * @param value string
     * @param split string
     */
    public function where($key, $value = '', $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (is_array($v) && count($v) == 2 && is_array($v[1])) {
                    $fun = $v[0];
                    if (is_string($k)) {
                        $args = array_merge(array($k), $v[1]);
                    } elseif (is_int($k)) {
                        $args = $v[1];
                    }
                    call_user_func_array(array($this, $fun), $args);
                } else {
                    $this->where($k, $v, $split);
                }
            }
        } elseif (is_array($value)) {
            $this->whereIn($key, $value, $split);
        } else {
            if (empty($value)) {
                $this->where .= ($this->where ? " $split " : '') . $key;
            } else {
                $where = $this->checkField($key) . " = " . $this->quote($value);
                $this->where .= ($this->where ? " $split " : '') . $where;
            }
        }
        return $this;
    }

    /**
     *
     * Set query clause WHERE with complex expression
     *
     * @param exp string
     * @param key string
     * @param value string
     * @param split string
     * @example whereExp(" ? = ? )", 'endtime', 0, 'OR');
     */
    public function whereRegexp($key, $value, $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        $where = $this->checkField($key) . " REGEXP " . $this->quote($value);
        $this->where .= ($this->where ? " $split " : '') . ($kh == '(' ? '(' : '') . $where . ($kh == ')' ? ')' : '');
        return $this;
    }

    /**
     *
     * Set query clause WHERE field != 'value'
     *
     * @param key string
     * @param value string
     * @param split string
     */
    public function whereNotEqual($key, $value, $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        $where = $this->checkField($key) . " != " . $this->quote($value);
        $this->where .= ($this->where ? " $split " : '') . $where;
        return $this;
    }

    /**
     *
     * Set query clause WHERE field >= 'value' or field > 'value'
     *
     * @param key string
     * @param value string
     * @param equal integer
     * @param split string
     * @example whereMore('total', '40000', 1, 'OR')
     */
    public function whereMore($key, $value, $equal = 1, $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        $mark = $equal ? '>=' : '>';
        $where = $this->chekc_field($key) . $mark . $this->quote($value);
        $this->where .= ($this->where ? " $split " : '') . $where;
        return $this;
    }

    /**
     *
     * Set query clause WHERE field <= 'value' OR field < 'value'
     *
     * @param key string
     * @param value string
     * @param equal integer
     * @param split string
     * @example whereLess('price', '200', 1, 'AND')
     */
    public function whereLess($key, $value, $equal = 1, $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        $mark = $equal ? '<=' : '<';
        $where = $this->checkField($key) . $mark . $this->quote($value);
        $this->where .= ($this->where ? " $split " : '') . $where;
        return $this;
    }

    /**
     *
     * Set query clause WHERE field IN value
     *
     * @param key string
     * @param values string
     * @param split string
     */
    public function whereIn($key, $values, $split = 'AND') {
        if (count($values) == 0) {
            return $this;
        }
        if (count($values) == 1) {
            return $this->where($key, $values[0], $split);
        }
        foreach ($values as $_key => $_val) {
            $values[$_key] = $this->quote($_val);
        }
        $where = $this->checkField($key) . " IN (" . implode(",", $values) . ")";
        $this->where .= ($this->where ? " $split " : '') . $where;
        return $this;
    }

    /**
     *
     * Set query clause WHERE field NOT IN value
     *
     * @param key string
     * @param values string
     * @param split string
     */
    public function whereNotIn($key, $values, $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        if (count($values) == 0) {
            return $this;
        }
        foreach ($values as $_key => $_val) {
            $values[$key] = $this->quote($_val);
        }
        $where = $this->checkField($key) . " NOT IN (" . implode(",", $values) . ")";
        $this->where .= ($this->where ? " $split " : '') . $where;
        return $this;
    }

    /**
     *
     * Set query clause WHERE field BETWEEN $begin AND $end
     *
     * @param key string
     * @param begin string
     * @param end string
     * @param split string
     */
    public function whereBetweenAnd($key, $begin, $end, $split = 'AND') {
        if (empty($key)) {
            return $this;
        }
        $where = $this->checkField($key) . " BETWEEN " . $this->quote($begin) . " AND " . $this->quote($end);
        $this->where .= ($this->where ? " $split " : '') . $where;
        return $this;
    }

    /**
     *
     * Set query clause WHERE field LIKE value
     *
     * @param key string
     * @param value string
     * @param split string
     * @param kh string
     */
    public function whereLike($key, $value, $split = 'AND', $kh = '') {
        if (empty($key)) {
            return $this;
        }
        $where = $this->checkField($key) . " LIKE " . $this->quote($value);
        $this->where .= ($this->where ? " $split " : '') . ($kh == '(' ? '(' : '') . $where . ($kh == ')' ? ')' : '');
        return $this;
    }

    /**
     *
     * Set query clause WHERE ... OR field = value
     *
     * @param key string
     * @param value string
     */
    public function whereOr($key, $value) {
        if (empty($key)) {
            return $this;
        }
        $where = $this->checkField($key) . " = " . $this->quote($value);
        $this->where .= ($this->where ? ' OR ' : '') . $where;
        return $this;
    }

    /**
     *
     * Set query clause WHERE CONCAT(field,field2) LIKE '100%'
     *
     * @param keys string
     * @param value string
     * @param split string
     * @param kh string
     */
    public function whereConcatLike($keys, $value, $split = 'AND', $kh = '') {
        if (empty($keys)) {
            return $this;
        }
        if (is_string($keys)) {
            $keys = explode(',', $keys);
        }
        foreach ($keys as $k => $v) {
            $keys[$k] = $this->checkField(trim($v));
        }
        $where = "CONCAT(" . implode(',', $keys) . ") LIKE " . $this->quote($value);
        $this->where .= ($this->where ? " $split " : '') . ($kh == '(' ? '(' : '') . $where . ($kh == ')' ? ')' : '');
        return $this;
    }

    /**
     *
     * Set query clause WHERE EXISTS
     *
     * @param sql string
     * @param split string
     * @param kh string
     */
    public function wehreExist($sql, $split = 'AND', $kh = '') {
        if (empty($sql)) {
            return $this;
        }
        $where = 'exists(' . $this->getTable($sql) . ')';
        $this->where .= ($this->where ? " $split " : '') . ($kh == '(' ? '(' : '') . $where . ($kh == ')' ? ')' : '');
    }

    /**
     *
     * Set query clause GROUP BY
     *
     * @param groups string
     */
    public function groupby($groups) {
        if (empty($groups)) {
            return $this;
        } elseif (is_array($groups)) {
            foreach ($groups as $key => $val) {
                $groups[$key] = $this->checkField($val);
            }
            $groupby = implode(',', $groups);
        } elseif (is_string($groups)) {
            $groupby = $this->checkField($groups);
        }
        $this->groupby .= ($this->groupby ? ',' : '') . $groupby;
        return $this;
    }

    /**
     *
     * Set query clause ORDER BY
     *
     * @param field string
     * @param type string
     */
    public function orderby($field, $type = 'ASC') {
        if (empty($field)) {
            return $this;
        } elseif (is_array($field)) {
            foreach ($field as $key => $val) {
                if (is_int($key)) {
                    call_user_func_array(array($this, __FUNCTION__), array($val));
                } else {
                    call_user_func_array(array($this, __FUNCTION__), array($key, $val));
                }
            }
        } elseif (is_string($field)) {
            $orderby = '';
            if (strpos($field, ',')) {
                $orderby .= $this->checkField($field) . ' ';
            } else {
                if (strpos($field, ' ')) {
                    list($field, $type) = explode(' ', $field);
                }
                $orderby .= $this->checkField($field) . ($type == 'DESC' ? (' ' . $type) : '');
            }
            $this->orderby .= ($this->orderby ? ', ' : '') . $orderby;
        }
        return $this;
    }

    /**
     *
     * Set query clause LIMIT
     *
     * @param start integer
     * @param offset integer
     */
    public function limit($start, $offset = '') {
        if ($start >= 0) {
            if (!$offset) {
                $this->limit = "$start OFFSET 0 ";
            } else {
                $this->limit = "$offset OFFSET $start ";
            }
        }
        return $this;
    }

    /**
     * Page
     * 
     * @param integer $page
     * @param integer $listRows
     * @return \DbQueryAbstract
     */
    public function page($page, $listRows = null) {
        if (is_null($listRows) && strpos($page, ',')) {
            list($page, $listRows) = explode(',', $page);
        }
        $page = $page > 0 ? $page : 1;
        $listRows = $listRows > 0 ? $listRows : 10;
        $offset = $listRows * ($page - 1);
        $this->limit = "$listRows OFFSET $offset ";
        return $this;
    }

    /**
     * TTL
     * 
     * @param type $ttl
     * @return \DbQueryAbstract
     */
    public function ttl($ttl) {
        if ($ttl === true) {
            //do nothing
        } elseif ($ttl > 0) {
            $this->ttl = $ttl;
        }
        return $this;
    }

    /**
     * Bluid SQL
     * 
     * @param type $type
     * @param type $getCountSql
     * @return string
     * @throws KantException
     */
    public function bluidSql($type, $getCountSql = false) {
        foreach ($this->varFields as $v) {
            $$v = $v;
        }
        if (!$this->from) {
            throw new KantException('Invalid SQL: FROM(DELETE)');
        }
        switch ($type) {
            case 'SELECT':
                $sql = "SELECT " . ($getCountSql ? "COUNT(*) as count" : ($this->select ? $this->select : "*")) .
                        " FROM " . $this->from .
                        ($this->where ? " WHERE " . $this->where : "") .
                        ($getCountSql ? '' : ($this->groupby ? " GROUP BY " . $this->groupby : "")) .
                        ($getCountSql ? '' : ($this->orderby ? " ORDER BY " . $this->orderby : "")) .
                        ($getCountSql ? '' : ($this->limit ? " LIMIT " . $this->limit : ""));

                break;
            case 'INSERT':
                if (empty($this->set)) {
                    throw new KantException('Invalid sql: SET(INSERT)');
                }
                $setsql = $setkey = $setval = '';
                $setkey = implode(',', array_keys($this->set));
                $setval = implode(',', array_values($this->set));
                $sql = "INSERT INTO " . $this->from . "($setkey)  VALUES ($setval)";
                break;
            case 'UPDATE';
                if (empty($this->set)) {
                    throw new KantException('Invalid sql: SET(INSERT)');
                }
                $split = $setsql = '';
                foreach ($this->set as $key => $val) {
                    $setsql .= $split . $key . ' = ' . $val;
                    $split = ', ';
                }
                $sql = "UPDATE " . $this->from . " SET " . $setsql . ($this->where ? " WHERE " . $this->where : "");
                break;
            case 'DELETE':
                $sql = "DELETE FROM " . $this->from . ($this->where ? " WHERE " . $this->where : "");
                break;
        }
        return $sql;
    }

    /**
     *
     * Clear SQL cache
     *
     * @param name string
     */
    public function clearFields() {
        foreach ($this->varFields as $v) {
            $this->$v = '';
        }
    }

    /**
     * Get query sqls
     * @return type
     */
    public function getLastSqls() {
        return $this->sqls;
    }

    /**
     * Flush cached sqls when too many
     */
    public function flushSqls() {
        $this->sqls = array();
    }

    /**
     *
     * Check field
     *
     * @param string $field
     * @return string
     */
    public function checkField($field) {
        if (preg_match("/[\'\\\"\<\>\/]+/", $field)) {
            throw new KantException(sprintf('Invalid field:%s', $field));
        }
        return $field;
    }

    /**
     * Safely quotes a value for an SQL statement.
     * 
     * @param mixed $str
     */
    public function quote($str) {
        switch (gettype($str)) {
            case 'string':
                $str = "'" . addslashes($str) . "'";
                break;
            case 'boolean':
                $str = ($str === false) ? 0 : 1;
                break;
            default:
                $str = ($str === null) ? 'null' : $str;
                break;
        }
        return $str;
    }

}

?>
