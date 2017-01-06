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
                    'db' => (new Connection($this->_dbConfig))->open(),
//                    'table' => $this->_dbConfig['tablepre'] . $this->table,
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

//    public function find($id) {
//        $this->db()->where("id = {$id}")->one();
//    }

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
    public static function getDb() {
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
