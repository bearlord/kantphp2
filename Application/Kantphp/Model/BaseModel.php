<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Model;

use Kant\Base;
use Kant\KantRegistry;
use Kant\KantException;
use Kant\Database\Driver;

!defined('IN_KANT') && exit('Access Denied');

require_once KANT_PATH . 'Database/Driver.php';
require_once KANT_PATH . 'Database/DbQueryAbstract.php';
require_once KANT_PATH . 'Database/DbQueryInterface.php';

/**
 * Base Model class
 * 
 * @access protected
 * 
 */
class BaseModel extends Base {

    //Db config
    private $_dbConfig;
    //Db connection
    protected $db = '';
    //Db setting
    protected $adapter = 'default';
    //Table name
    protected $table;
    //Table key
    protected $primary = 'id';
    //Table field
    private $_field = array();
    //Table field filter function
    protected $fieldFunc = array();

    /**
     *
     * Construct
     *
     */
    public function __construct() {
        parent::__construct();
        $this->getDbo();
    }

    /**
     *
     * Get a database object.
     * 
     */
    public function getDbo() {
        if ($this->db == '') {
            $this->createDbo();
        }
    }

    /**
     * 
     * Create an database object
     * 
     */
    public function createDbo() {
        $config = KantRegistry::get('config');
        $this->_dbConfig = $config['database'];
        if (!isset($this->_dbConfig[$this->adapter])) {
            $this->adapter = 'default';
        }       
        try {
            $this->db = Driver::getInstance($this->_dbConfig)->getDatabase($this->adapter);
        } catch (KantException $e) {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }
            exit('Database Error: ' . $e->getMessage());
        }
    }

    /**
     * Validate data using regular
     * 
     * @access public
     * @param string $value  
     * @param string $rule 
     * @return boolean
     */
    public function regex($value, $rule) {
        $validate = array(
            'require' => '/.+/',
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url' => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number' => '/^\d+$/',
            'zip' => '/^\d{6}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
        );
        // Check whether there is a built-in regular expression
        if (isset($validate[strtolower($rule)])) {
            $rule = $validate[strtolower($rule)];
        }
        return preg_match($rule, $value) === 1;
    }

    public function check($value, $rule, $type = 'regex') {
        $type = strtolower(trim($type));
        switch ($type) {
            //Verify whether value is in a specified range, comma-delimited string or array
            case 'in':
            case 'notin':
                $range = is_array($rule) ? $rule : explode(',', $rule);
                return $type == 'in' ? in_array($value, $range) : !in_array($value, $range);
            //Verify whether value is in a specified range,
            case 'between':
            //Verify whether value is in a specified range,
            case 'notbetween':
                if (is_array($rule)) {
                    $min = $rule[0];
                    $max = $rule[1];
                } else {
                    list($min, $max) = explode(',', $rule);
                }
                return $type == 'between' ? $value >= $min && $value <= $max : $value < $min || $value > $max;
            //Verify whether value is equal to 123
            case 'equal':
            //Verify whether is not equal to 123
            case 'notequal':
                return $type == 'equal' ? $value == $rule : $value != $rule;
            //Verify value's length
            case 'length':
                $length = mb_strlen($value, 'utf-8');
                if (strpos($rule, ',')) { // 长度区间
                    list($min, $max) = explode(',', $rule);
                    return $length >= $min && $length <= $max;
                } else {// 指定长度
                    return $length == $rule;
                }
            //Verify whether value is expired
            case 'expire':
                list($start, $end) = explode(',', $rule);
                if (!is_numeric($start)) {
                    $start = strtotime($start);
                }
                if (!is_numeric($end)) {
                    $end = strtotime($end);
                }
                $_time = time();
                return $_time >= $start && $_time <= $end;
            //Verify whether ip is in the allow list
            case 'ip_allow':
                return in_array(get_client_ip(), explode(',', $rule));
            //Verify whether is in the ip deny list
            case 'ip_deny': // IP 操作禁止验证
                return !in_array(get_client_ip(), explode(',', $rule));
            //Regex method
            case 'regex':
            default:
                return $this->regex($value, $rule);
        }
    }

    public function validation($data, $redirect = false) {
        if (empty($data)) {
            return;
        }
        if (is_string($data[0])) {
            $val = $data;
            if ($this->check($val[0], $val[1], $val[2]) == false) {
                if ($redirect) {
                    $this->redirect($val[3]);
                }
                $result['status'] = 'error';
                $result['message'] = $val[3];
                return $result;
            }
        } else {
            foreach ($data as $key => $val) {
                if ($this->check($val[0], $val[1], $val[2]) == false) {
                    if ($redirect) {
                        $this->redirect($val[3]);
                    }
                    $result['status'] = 'error';
                    $result['message'] = $val[3];
                    return $result;
                }
            }
        }
    }

    /**
     *
     * Get data from the current table
     *
     * @param value string,array
     * @param select string,array
     * @param key string
     * @return result array
     */
    public function read($select = "*", $keyid = '', $value = '') {
        $where = array();
        if ($keyid == '') {
            $where[$this->primary] = $value;
        } elseif (is_string($keyid)) {
            $where[$keyid] = $value;
        } elseif (is_array($keyid)) {
            $where = $keyid;
        }
        $result = $this->db->fetchEasy($select, $this->table, $where);
        return $result;
    }

    /**
     *
     * Get all data from the currnet table
     *
     * @param select string,array
     * @param orderby string,array
     * @param total boolean
     * @return result array
     */
    public function readAll($select = "*", $orderby = null) {
        $this->db->clear();
        $result = $this->db->from($this->table)->select($select)->orderby($orderby)->fetch();
        return $result;
    }

    /**
     *
     * Get the data of the current table under certain constraints
     *
     * @param select string,array
     * @param where string,array
     * @param orderby string,array
     * @param start integer
     * @param offset integer
     * @param total boolean
     * @return result array
     */
    public function find($select = "*", $where = null, $orderby = null, $start = 0, $offset = 10, $total = false) {
        $result = array(0, '');
        $result[0] = $this->db->from($this->table)->select($select)->where($where)->orderby($orderby)->limit($start, $offset)->fetch();
        if ($total) {
            $this->db->sqlRollback('from,where');
            $result[1] = $this->db->count();
        }
        return $result;
    }

    /**
     *
     * Delete data from the current table
     *
     * @param ids string,array
     * @param cache boolean
     */
    public function delete($keyid) {
        if ($keyid == '') {
            return;
        }
        $this->db->from($this->table);
        if (is_array($keyid)) {
            if (empty($keyid[0])) {
                $this->db->where($keyid);
            } else {
                $this->db->whereIn($this->primary, $keyid);
            }
        } else {
            $this->db->where($this->primary, $keyid);
        }
        return $this->db->delete();
    }

    /**
     *
     * Save data
     *
     * @param post array
     * @param keyid string
     * @param check boolean
     * @param convert boolean
     *
     * @return keyid integer or boolean true,false
     */
    public function save($post, $keyid = null) {
        if (is_array($post) == false) {
            return;
        }
        $this->db->from($this->table);
        foreach ($post as $k => $v) {
            $this->db->set($k, $v);
        }
        // save/update data
        if ($keyid) {
            if (is_array($keyid)) {
                return $this->db->where($keyid)->update();
            } else {
                return $this->db->where($this->primary, $keyid)->update();
            }
        } else {
            $this->db->insert();
            return $this->db->lastInsertId();
        }
    }

    /**
     *
     * get post data
     *
     * @param post array
     * @return post array
     */
    public function getPost(&$post) {
        if (!$this->field) {
            return false;
        }
        $result = array();
        foreach ($this->field as $key) {
            if (isset($post[$key])) {
                $result[$key] = $post[$key];
            }
        }
        return $result;
    }

    /**
     *
     * Convert post data
     *
     * @param post array
     * @return post array
     */
    private function convertPost(&$post) {
        if (!$this->fieldFunc) {
            return $post;
        }
        foreach ($this->fieldFunc as $key => $func) {
            if (!isset($post[$key])) {
                continue;
            }
            $post[$key] = $func($post[$key]);
        }
        return $post;
    }

    /**
     *
     * Check post
     *
     * @param post array
     * @param edit boolean
     */
    public function checkPost(&$post, $edit = false) {
        
    }

    /**
     *
     * Page list
     *
     * @param select string
     * @param where array
     * @param orderby string
     * @param page integer
     * @param offset integer
     * @return array
     */
    public function pageList($select = "*", $where = null, $orderby = null, $page = 1, $offset = 10) {
        $start = $this->getStart($page, $offset);
        return $this->find($select, $where, $orderby, $start, $offset, true);
    }

    /**
     *
     * Convert page number to subscript
     *
     * @param page integer
     * @param offset integer
     */
    public function getStart($page, $offset) {
        if (!$offset || $offset < 1) {
            $offset = 10;
        }
        if ($page < 1) {
            $page = 1;
        }
        return ($page - 1) * $offset;
    }

    /**
     *
     * Execute the original ecology of SQL
     *
     * @param SQL string
     * @param method string
     */
    public function query($SQL, $method = 'SILENT') {
        $result = $this->db->query($SQL, $method);
        return $this->db->fetch($result);
    }

    public function lastSqls() {
        return $this->db->getLastSqls();
    }

}
