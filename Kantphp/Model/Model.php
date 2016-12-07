<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Model;

use Kant\Foundation\Component;
use Kant\Exception\KantException;
use Kant\KantFactory;
use Kant\Database\Connection;
use Kant\Database\Query;
use BadMethodCallException;
use Kant\Cache\Cache;

/**
 * Base Model class
 * 
 * @access protected
 * 
 */
class Model extends Component {

    const MODEL_INSERT = 1;
    const MODEL_UPDATE = 2;
    const MODEL_BOTH = 3;      //  包含上面两种方式
    const EXISTS_VALIDATE = 0;
    const MUST_VALIDATE = 1;
    const VALUE_VALIDATE = 2;

    /**
     * Db config
     */
    private $_dbConfig;

    /**
     *
     * @var array database link pool
     */
    protected static $pools = [];

    /**
     * Db connection
     */
    protected $db = '';

    /**
     * Db setting
     */
    protected $adapter = 'default';

    /**
     * Table name
     */
    protected $table;

    /**
     * Class
     */
    protected $class;

    /**
     * Table key
     */
    protected $primary = 'id';

    /**
     * Data
     */
    protected $data;

    /**
     * Fields
     */
    protected $fields = [];

    /**
     * Fields cache name
     */
    protected $fieldsCacheName;

    /**
     * Options
     */
    protected $options = [];

    /**
     * Method lists
     */
    protected $methods = ['validate', 'token'];

    /**
     * Patch validate
     */
    protected $patchValidate = false;

    /**
     * Error
     */
    protected $error = "";
    //Auto check Fields
    protected $autoCheckFields = true;
    //array attribute values indexed by attribute names
    private $_attributes = [];
    //array related models indexed by the relation names
    private $_related = [];

    /**
     *
     * Construct
     *
     */
    public function __construct($data = []) {
        if (is_object($data)) {
            $this->data = get_object_vars($data);
        } else {
            $this->data = $data;
        }
        $this->class = get_called_class();
    }

    /**
     * Database instance
     * @return object
     */
    public function db() {
        $model = $this->class;
        if (!isset(self::$pools[$model])) {
            $this->_dbConfig = KantFactory::getConfig()->get('database.' . $this->adapter);
            try {
                $query = new Query([
                    'db' => Connection::open($this->_dbConfig),
                    'table' => $this->_dbConfig['tablepre'] . $this->table,
                    'model' => $model
                ]);
            } catch (KantException $e) {
                throw new KantException('Database Error: ' . $e->getMessage());
            }
            self::$pools[$model] = $query;
        }
        // 返回当前模型的数据库查询对象
        return self::$pools[$model];
    }

    /**
     * Check table infomation
     */
    private function _checkTableInfo() {
        if (empty($this->fields)) {
            $fieldsCache = KantFactory::getConfig()->get('db_fields_cache');
            if ($fieldsCache) {
                $this->fields = Cache::get($this->fieldsCacheName);
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
        if (!$fields) {
            return false;
        }
        $this->fields = array_keys($fields);
        foreach ($fields as $key => $val) {
            $type[$key] = $val['type'];
            $this->fields['_type'] = $type;
        }
        $fieldsCache = KantFactory::getConfig()->get('db_fields_cache');
        if ($fieldsCache) {
            Cache::set($this->fieldsCacheName, $this->fields);
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
                array_walk($fields, "ctrim");
            }
            $tokenConfig = KantFactory::getConfig()->get('token');
            if ($tokenConfig['switch']) {
                $fields[] = $tokenConfig['name'];
            }
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                }
            }
        }
        //Auto validation
        if (!$this->autoValidation($data, $type)) {
            return false;
        }
        //Auto check token
        if (!$this->autoCheckToken($data)) {
            $this->error = $this->lang("token_error");
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
        return true;
    }

    /**
     * Auto check token
     * 
     * @return boolean
     */
    protected function autoCheckToken($data) {
        // token(false)
        if (isset($this->options['token']) && $this->options['token'] === false) {
            return true;
        }
        $tokenConfig = KantFactory::getConfig()->get('token');
        if ($tokenConfig['switch']) {
            $name = !empty($tokenConfig['name']) ? $tokenConfig['name'] : "__hash__";
            if (!isset($data[$name]) || !isset($_SESSION[$name])) {
                return false;
            }
            list($key, $value) = explode('_', $data[$name]);
            if (isset($_SESSION[$name][$key]) && $value && $_SESSION[$name][$key] === $value) {
                unset($_SESSION[$name][$key]);
                return true;
            }
            if ($tokenConfig['reset']) {
                unset($_SESSION[$name][$key]);
            }
            return false;
        }
        return true;
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
                if ($this->read("*", $map)) {
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
                if (strpos($rule, ',')) {
                    list($min, $max) = explode(',', $rule);
                    return $length >= $min && $length <= $max;
                } else {
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
//    public function find($select = "*", $where = null, $orderby = null, $start = 0, $offset = 10, $total = false) {
//        $result = array(0, '');
//        $result[0] = $this->db->from($this->table)->select($select)->where($where)->orderby($orderby)->limit($start, $offset)->fetch();
//        if ($total) {
//            $result[1] = $this->db->from($this->table)->where($where)->count();
//        }
//        return $result;
//    }

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
     * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
     */
    public function ensureBehaviors() {
        if ($this->_behaviors === null) {
            $this->_behaviors = [];
            $behaviors = $this->behaviors();
            if (!empty($behaviors)) {
                foreach ($this->behaviors() as $name => $behavior) {
                    $this->attachBehaviorInternal($name, $behavior);
                }
            }
        }
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
     * Calls the named method which is not a class method.
     *
     * This method will check if any attached behavior has
     * the named method and will execute it if available.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     * @param string $method the method name
     * @param array $params method parameters
     * @return mixed the method return value
     */
    public function __call($method, $params) {
        var_dump($method);
        var_dump($this->db());
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $object) {
            if ($object->hasMethod($method)) {
                return call_user_func_array([$object, $method], $params);
            }
        }

        return call_user_func_array([$this->db(), $method], $params);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $params
     * @return mixed
     */
    public static function __callStatic($method, $params) {
        $query = self::getDb();
        return call_user_func_array([$query, $method], $params);
    }

    /**
     * Static method get db instance
     * 
     * @return object
     */
    protected static function getDb() {
        $model = get_called_class();
        if (!isset(self::$pools[$model])) {
            self::$pools[$model] = (new static())->db();
        }
        return self::$pools[$model];
    }

    /**
     * PHP getter magic method.
     * This method is overridden so that attributes and related objects can be accessed like properties.
     *
     * @param string $name property name
     * @throws \InvalidParamException if relation name is wrong
     * @return mixed property value
     * @see getAttribute()
     */
    public function __get($name) {
        if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } elseif ($this->hasAttribute($name)) {
            return null;
        } else {
            if (isset($this->_related[$name]) || array_key_exists($name, $this->_related)) {
                return $this->_related[$name];
            }
            $value = parent::__get($name);
            if ($value instanceof ActiveQueryInterface) {
                return $this->_related[$name] = $value->findFor($name, $this);
            } else {
                return $value;
            }
        }
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that AR attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __set($name, $value) {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking if the named attribute is null or not.
     * @param string $name the property name or the event name
     * @return boolean whether the property value is null
     */
    public function __isset($name) {
        try {
            return $this->__get($name) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sets a component property to be null.
     * This method overrides the parent implementation by clearing
     * the specified attribute value.
     * @param string $name the property name or the event name
     */
    public function __unset($name) {
        if ($this->hasAttribute($name)) {
            unset($this->_attributes[$name]);
        } elseif (array_key_exists($name, $this->_related)) {
            unset($this->_related[$name]);
        } elseif ($this->getRelation($name, false) === null) {
            parent::__unset($name);
        }
    }

    /**
     * Returns a value indicating whether the model has an attribute with the specified name.
     * @param string $name the name of the attribute
     * @return boolean whether the model has an attribute with the specified name.
     */
    public function hasAttribute($name) {
        return isset($this->_attributes[$name]) || in_array($name, $this->attributes(), true);
    }

    /**
     * Returns the named attribute value.
     * If this record is the result of a query and the attribute is not loaded,
     * null will be returned.
     * @param string $name the attribute name
     * @return mixed the attribute value. Null if the attribute is not set or does not exist.
     * @see hasAttribute()
     */
    public function getAttribute($name) {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    /**
     * Sets the named attribute value.
     * @param string $name the attribute name
     * @param mixed $value the attribute value.
     * @throws InvalidParamException if the named attribute does not exist.
     * @see hasAttribute()
     */
    public function setAttribute($name, $value) {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        } else {
            throw new BadMethodCallException(get_class($this) . ' has no attribute named "' . $name . '".');
        }
    }

    /**
     * Returns the list of attribute names.
     * By default, this method returns all public non-static properties of the class.
     * You may override this method to change the default behavior.
     * @return array list of attribute names.
     */
    public function attributes() {
        $class = new \ReflectionClass($this);
        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }

}
