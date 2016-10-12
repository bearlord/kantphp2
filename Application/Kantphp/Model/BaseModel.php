<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Model;

use Kant\Base;
use Kant\Exception\KantException;
use Kant\KantFactory;
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

    const MODEL_INSERT = 1;
    const MODEL_UPDATE = 2;
    const EXISTS_VALIDATE = 0;
    const MUST_VALIDATE = 1;
    const VALUE_VALIDATE = 2;

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
    //Data
    protected $data;
    //Fields
    protected $fields = [];
    //Fields cache name
    protected $fieldsCacheName;
    //Options
    protected $options = [];
    //Method lists
    protected $methods = ['validate', 'token'];
    //Patch validate
    protected $patchValidate = false;
    //Error
    protected $error = "";
    //Auto check Fields
    protected $autoCheckFields = true;

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
        $config = KantFactory::getConfig()->reference();
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
        if (!empty($this->table) && $this->autoCheckFields) {
            $this->fieldsCacheName = "fields_" . $this->_dbConfig[$this->adapter]['database'] . "_" . $this->table;
            $this->_checkTableInfo();
        }
    }

    /**
     * Check table infomation
     */
    private function _checkTableInfo() {
        if (empty($this->fields)) {
            $dbFieldsCache = KantFactory::getConfig()->get('db_fields_cache');
            if ($dbFieldsCache) {
                $this->fields = $this->cache->get($this->fieldsCacheName);
                return;
            }
            $this->flushTableInfo();
        }
    }

    /**
     * Flush table infomation and cache
     */
    public function flushTableInfo() {
        $fields = $this->db->getFields($this->table);
        if (!$fields) { // 无法获取字段信息
            return false;
        }
        $this->fields = array_keys($fields);
        foreach ($fields as $key => $val) {
            $type[$key] = $val['type'];
            $this->fields['_type'] = $type;
        }
        $dbFieldsCache = KantFactory::getConfig()->get('db_fields_cache');
        if ($dbFieldsCache) {
            $this->cache->set($this->fieldsCacheName, $this->fields);
        }
    }

    /**
     * ORM Create
     * 
     * @param type $data
     * @param type $type
     * @return boolean
     */
    public function create($data, $type = '') {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (empty($data) || !is_array($data)) {
            $this->error = $this->lang('data_type_invalid');
            return false;
        }
        $type = $type ? : (!empty($data[$this->primary]) ? self::MODEL_UPDATE : self::MODEL_INSERT);

        if (isset($this->options['field'])) { // $this->field('field1,field2...')->create()
            $fields = $this->options['field'];
            unset($this->options['field']);
        } elseif ($type == self::MODEL_INSERT && isset($this->insertFields)) {
            $fields = $this->insertFields;
        } elseif ($type == self::MODEL_UPDATE && isset($this->updateFields)) {
            $fields = $this->updateFields;
        }

        if (isset($fields)) {
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                }
            }
        }

        if ($this->autoValidation($data, $type)) {
            return false;
        }
        $this->data = $data;
        return $data;
    }

    /**
     * Auto Validation
     * 
     * @param type $data
     * @param type $type
     */
    protected function autoValidation($data, $type) {
        if (false === $this->options['validate']) {
            return true;
        }
        if (!empty($this->options['validate'])) {
            $_validate = $this->options['validate'];
            unset($this->options['validate']);
        }
        if (isset($_validate)) {
            if ($this->patchValidate) {
                $this->error = array();
            }
            foreach ($_validate as $key => $val) {
                // array(field,rule,message,condition,type,when,params)
                if (empty($val[5]) || ( $val[5] == self::MODEL_BOTH && $type < 3 ) || $val[5] == $type) {
                    if (0 == strpos($val[2], '{%') && strpos($val[2], '}')) {
                        $val[2] = $this->lang(substr($val[2], 2, -1));
                    }
                    $val[3] = isset($val[3]) ? $val[3] : self::EXISTS_VALIDATE;
                    $val[4] = isset($val[4]) ? $val[4] : 'regex';
                    switch ($val[3]) {
                        case self::MUST_VALIDATE:
                            if (false === $this->_validationField($data, $val)) {
                                return false;
                            }
                            break;
                        case self::VALUE_VALIDATE:
                            if ('' != trim($data[$val[0]])) {
                                if (false === $this->_validationField($data, $val)) {
                                    return false;
                                }
                            }
                            break;
                        default:
                            if (isset($data[$val[0]])) {
                                echo 111;
                                if (false === $this->_validationField($data, $val)) {
                                    return false;
                                }
                            }
                    }
                }
            }
            if (!empty($this->error)) {
                return false;
            }
        }
    }

    /**
     * Field Validation
     * 
     * @param type $data
     * @param type $val
     * @return boolean
     */
    protected function _validationField($data, $val) {
        if ($this->patchValidate && isset($this->error[$val[0]])) {
            return;
        }
        if (false === $this->_validationFieldItem($data, $val)) {
            if ($this->patchValidate) {
                $this->error[$val[0]] = $val[2];
            } else {
                $this->error = $val[2];
                return false;
            }
        }
        return;
    }

    protected function _validationFieldItem($data, $val) {
        switch (strtolower(trim($val[4]))) {
            case 'function':
            case 'callback':
                $args = isset($val[6]) ? (array) $val[6] : array();
                if (is_string($val[0]) && strpos($val[0], ',')) {
                    $val[0] = explode(',', $val[0]);
                }
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field) {
                        $_data[$field] = $data[$field];
                    }
                    array_unshift($args, $_data);
                } else {
                    array_unshift($args, $data[$val[0]]);
                }
                if ('function' == $val[4]) {
                    return call_user_func_array($val[1], $args);
                } else {
                    return call_user_func_array(array(&$this, $val[1]), $args);
                }
            case 'confirm':
                return $data[$val[0]] == $data[$val[1]];
            case 'unique':
                if (is_string($val[0]) && strpos($val[0], ',')) {
                    $val[0] = explode(',', $val[0]);
                }
                $map = array();
                if (is_array($val[0])) {
                    foreach ($val[0] as $field) {
                        $map[$field] = $data[$field];
                    }
                } else {
                    $map[$val[0]] = $data[$val[0]];
                }
                if (!empty($data[$this->primary]) && is_string($this->primary)) {
                    $map[$this->primary] = array('whereNot', $data[$this->primary]);
                }
                if ($this->where($map)->find()) {
                    return false;
                }
                return true;
            default:  // 检查附加规则
                $tempdata = isset($data[$val[0]]) ? $data[$val[0]] : "";
                return $this->check($tempdata, $val[1], $val[4]);
        }
    }

    /**
     * Check
     * 
     * @param type $value
     * @param type $rule
     * @param type $type
     * @return type
     */
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

    /**
     * Get error
     * @return type
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Set Field 
     * @param type $field
     * @param type $except
     * @return 
     */
    public function field($field, $except = false) {
        if (true === $field) {
            $fields = $this->getDbFields();
            $field = $fields? : '*';
        } elseif ($except) {
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            $fields = $this->getDbFields();
            $field = $fields ? array_diff($fields, $field) : $field;
        }
        $this->options['field'] = $field;
        return $this;
    }

    /**
     * Get Database Fields
     * @return boolean
     */
    public function getDbFields() {
        if ($this->fields) {
            $fields = $this->fields;
            return $fields;
        }
        $fields = $this->db->getFields($this->table);
        return $fields ? array_keys($fields) : false;
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
            $result[1] = $this->db->from($this->table)->where($where)->count();
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
     * @param data array
     * @param keyid string
     * @return keyid integer or boolean true,false
     */
    public function save($data = "", $keyid = null) {
        if (empty($data)) {
            if (!empty($this->data)) {
                $data = $this->data;
                $this->data = array();
            } else {
                $this->error = $this->lang("data_type_invalid");
                return false;
            }
        }
        $data = $this->_facade($data);
        if (empty($data)) {
            $this->error = $this->lang("data_type_invalid");
            return false;
        }
        $this->db->from($this->table);
        foreach ($data as $k => $v) {
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

    /**
     * Get last query sqls
     * 
     * @return type
     */
    public function lastSqls() {
        return $this->db->getLastSqls();
    }

    /**
     * Facade data
     * 
     * @param array $data
     */
    private function _facade($data) {
        if (!empty($this->fields)) {
            if (!empty($this->options['field'])) {
                $fields = $this->options['field'];
                unset($this->options['field']);
                if (is_string($fields)) {
                    $fields = explode(',', $fields);
                }
            } else {
                $fields = $this->fields;
            }
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields, true)) {
                    unset($data[$key]);
                } elseif (is_scalar($val)) {
                    $this->_parseType($data, $key);
                }
            }
            return $data;
        }
    }

    protected function _parseType(&$data, $key) {
        if (isset($this->fields['_type'][$key])) {
            $fieldType = strtolower($this->fields['_type'][$key]);
            if (false !== strpos($fieldType, 'enum')) {
                //do nothing
            } elseif (false === strpos($fieldType, 'bigint') && false !== strpos($fieldType, 'int')) {
                $data[$key] = intval($data[$key]);
            } elseif (false !== strpos($fieldType, 'float') || false !== strpos($fieldType, 'double')) {
                $data[$key] = floatval($data[$key]);
            } elseif (false !== strpos($fieldType, 'bool')) {
                $data[$key] = (bool) $data[$key];
            }
        }
    }

    /**
     * Magic set
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * Magci get
     * @param type $name
     * @return type
     */
    public function __get($name) {
        return $this->data[$name];
    }

    /**
     * Magic isset
     * 
     * @param type $name
     * @return type
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     * Magic unset
     * 
     * @param type $name
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     * Magic call
     * 
     * @param type $method
     * @param type $args
     * @return \Kant\Model\BaseModel
     */
    public function __call($method, $args) {
        if (in_array(strtolower($method), $this->methods, true)) {
            $this->options[strtolower($method)] = $args[0];
            return $this;
        }
    }

}
