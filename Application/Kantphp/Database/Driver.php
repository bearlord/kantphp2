<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database;

use Kant\KantException;

!defined('IN_KANT') && exit('Access Denied');

/**
 * Database driver class
 * 
 * @access private
 * @final
 */
final class Driver {

    /**
     *
     * Static instance of factory mode
     *
     */
    private static $_database;

    /**
     *
     * Database config list
     *
     */
    private $_dbConfig = array();

    /**
     *
     * Database list
     *
     */
    private $_dbList = array();

    /**
     *
     * Construct
     *
     */
    public function __construct() {
        
    }

    /**
     *
     * Get instantce of the final object
     *
     * @param string $dbConfig
     * @return object on success
     */
    public static function getInstance($dbConfig = '') {
        if ($dbConfig == '') {
            $config = KantRegistry::get('config');
            $dbConfig = $config['database'];
        }
        if (self::$_database == '') {
            self::$_database = new self();
        }
        if ($dbConfig != '' && $dbConfig != self::$_database->_dbConfig) {
            self::$_database->_dbConfig = array_merge($dbConfig, self::$_database->_dbConfig);
        }
        return self::$_database;
    }

    /**
     *
     * Get instance of the _database config
     *
     * @param String db_name
     * @return resource a _database link identifier on success, or false on
     * failure.
     */
    public function getDatabase($db_name) {
        static $i;
        $i++;
        if ($i > 3) {
            return;
        }
        if (!isset($this->_dbList[$db_name]) || !is_object($this->_dbList[$db_name])) {
            $this->_dbList[$db_name] = $this->connect($db_name);
        }
        return $this->_dbList[$db_name];
    }

    /**
     *
     *  Load database driver
     *
     * @param db_name string
     * @return object on success
     */
    public function connect($db_name) {
        switch ($this->_dbConfig[$db_name]['type']) {
            case 'mysql' :
                require_once KANT_PATH . 'Database/MySQL/MysqlDb.php';
                $namespace = "Kant\\Database\\MySQL\\";
                $class = 'MysqlDb';
                break;
            case 'pdo_mysql' :
                require_once KANT_PATH . 'Database/PDO/MysqlDb.php';
                $namespace = "Kant\\Database\\PDO\\";
                $class = $namespace . 'MysqlDb';
                break;
            case 'pdo_sqlite';
                require_once KANT_PATH . 'Database/PDO/SqliteDb.php';
                $namespace = "Kant\\Database\\PDO\\";
                $class = $namespace . 'SqliteDb';
                break;
            case 'pdo_pgsql':
                require_once KANT_PATH . 'Database/PDO/PgsqlDb.php';
                $namespace = "Kant\\Databas\\PDO\\";
                $class = $namespace . 'PgsqlDb';
                break;
            case 'default':
                require_once KANT_PATH . 'Database/PDO/PgsqlDb.php';
                $namespace = "Kant\\Databas\\PDO\\";
                $class = $namespace . 'PgsqlDb';
                break;
        }
        if (!class_exists($class)) {
            throw new KantException(sprintf('Unable to load Database Driver: %s', $this->_dbConfig[$db_name]['type']));
        }
        $object = new $class;
        $object->open($this->_dbConfig[$db_name]);
        $object->dbTablepre = $this->_dbConfig[$db_name]['tablepre'];
        return $object;
    }

    /**
     *
     * Close _database connection
     *
     */
    protected function close() {
        foreach ($this->_dbList as $db) {
            $db->close();
        }
    }

    /**
     *
     * Destruct
     */
    public function __destruct() {
        $this->close();
    }

}
