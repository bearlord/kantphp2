<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database;

use Kant\Foundation\Component;
use Kant\Exception\KantException;
use Kant\KantFactory;
use InvalidArgumentException;

class Connection extends Component {

    /**
     *
     * Static instance of factory mode
     *
     */
    private static $_database;
    protected $pdo;
    public $options;
//    protected $dbh;
    private $_schema;
    protected $numRows = 0;
    protected $ttl;
    protected $schemaMap = [
        'pgsql' => \Kant\Database\Pgsql\Schema::class,
        'mysqli' => \Kant\Database\Mysql\Schema::class,
        'mysql' => \Kant\Database\Mysql\Schema::class,
    ];

    /**
     * @var string the class used to create new database [[Command]] objects. If you want to extend the [[Command]] class,
     * you may configure this property to use your extended version of the class.
     */
    public $commandClass = \Kant\Database\Command::class;

    public function __construct($options = []) {
        $this->options = $options;
    }

    /**
     *
     * Get instantce of the final object
     *
     * @param string $options
     * @return object on success
     */
    public static function open($options = '') {
        $name = md5(serialize($options));
        if (empty(self::$_database[$name])) {
            self::$_database[$name] = (new self($options))->openInternal();
        }
        return self::$_database[$name];
    }

    /**
     *
     *  Load database driver
     *
     * @param db_name string
     * @return object on success
     */
    public function openInternal($config = "") {
        $options = $this->parseConfig($config);
        if (empty($options['type'])) {
            throw new InvalidArgumentException('Underfined db type');
        }
        $class = "Kant\\Database\\" . ucfirst($options['type']) . "\\Connection";
        if (!class_exists($class)) {
            throw new KantException(sprintf('Unable to load Database Driver: %s', $options['type']));
        }
        $this->pdo = (new $class($options));
        $this->pdo->connect();
        return $this->pdo;
    }

    /**
     * Parse Config
     * 
     * @param array/string $config
     * @return array/string
     */
    protected function parseConfig($config = "") {
        if ($config == '') {
            $config = KantFactory::getConfig()->get('database.default');
        } elseif (is_string($config) && false === strpos($config, '/')) {
            $config = KantFactory::getConfig()->get('database.' . $config);
        }

        if (is_string($config)) {
            return $this->parseDsn($config);
        } else {
            return $config;
        }
    }

    /**
     * Parse DSN config
     * 
     * @param array $str
     */
    protected function parseDsn($str) {
        $info = parse_url($str);
        if (!$info) {
            return [];
        }
        $dsn = [
            'type' => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'hostname' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '',
            'database' => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset' => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];

        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dsn['params'] = [];
        }
        return $dsn;
    }

    /**
     * Creates a command for execution.
     * @param string $sql the SQL statement to be executed
     * @param array $params the parameters to be bound to the SQL statement
     * @return Command the DB command
     */
    public function createCommand($sql = null, $params = []) {
        /** @var Command $command */
        $command = new $this->commandClass([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }

    /**
     * Returns the schema information for the database opened by this connection.
     * @return Schema the schema information for the database opened by this connection.
     * @throws NotSupportedException if there is no support for the current driver type
     */
    public function getSchema() {
        if ($this->_schema !== null) {
            return $this->_schema;
        } else {
            $driver = $this->getDriverName();
            if (!empty(($this->schemaMap[$driver]))) {
                return $this->_schema = new $this->schemaMap[$driver](['db' => $this]);
            } else {
                throw new NotSupportedException("Connection does not support reading schema information for '$driver' DBMS.");
            }
        }
    }

    public function getDriverName() {
        return $this->options['type'];
    }

    /**
     * Returns the query builder for the current DB connection.
     * @return QueryBuilder the query builder for the current DB connection.
     */
    public function getQueryBuilder() {
        return $this->getSchema()->getQueryBuilder();
    }

    /**
     * Obtains the schema information for the named table.
     * @param string $name table name.
     * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
     * @return TableSchema table schema information. Null if the named table does not exist.
     */
    public function getTableSchema($name, $refresh = false) {
        return $this->getSchema()->getTableSchema($name, $refresh);
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     * @param string $sequenceName name of the sequence object (required by some DBMS)
     * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
     */
    public function getLastInsertID($sequenceName = '') {
        return $this->getSchema()->getLastInsertID($sequenceName);
    }

    /**
     * Quotes a string value for use in a query.
     * Note that if the parameter is not a string, it will be returned without change.
     * @param string $value string to be quoted
     * @return string the properly quoted string
     * @see http://www.php.net/manual/en/function.PDO-quote.php
     */
    public function quoteValue($value) {
        return $this->getSchema()->quoteValue($value);
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * If the table name is already quoted or contains special characters including '(', '[[' and '{{',
     * then this method will do nothing.
     * @param string $name table name
     * @return string the properly quoted table name
     */
    public function quoteTableName($name) {
        return $this->getSchema()->quoteTableName($name);
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * If the column name is already quoted or contains special characters including '(', '[[' and '{{',
     * then this method will do nothing.
     * @param string $name column name
     * @return string the properly quoted column name
     */
    public function quoteColumnName($name) {
        return $this->getSchema()->quoteColumnName($name);
    }

    /**
     * Processes a SQL statement by quoting table and column names that are enclosed within double brackets.
     * Tokens enclosed within double curly brackets are treated as table names, while
     * tokens enclosed within double square brackets are column names. They will be quoted accordingly.
     * Also, the percentage character "%" at the beginning or ending of a table name will be replaced
     * with [[tablePrefix]].
     * @param string $sql the SQL to be quoted
     * @return string the quoted SQL
     */
    public function quoteSql($sql) {
        return preg_replace_callback(
                '/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])/', function ($matches) {
            if (isset($matches[3])) {
                return $this->quoteColumnName($matches[3]);
            } else {
                return str_replace('%', $this->tablePrefix, $this->quoteTableName($matches[2]));
            }
        }, $sql
        );
    }

    /**
     * Changes the current driver name.
     * @param string $driverName name of the DB driver
     */
    public function setDriverName($driverName) {
        $this->_driverName = strtolower($driverName);
    }

    /**
     * Returns the PDO instance for the currently active slave connection.
     * When [[enableSlaves]] is true, one of the slaves will be used for read queries, and its PDO instance
     * will be returned by this method.
     * @param boolean $fallbackToMaster whether to return a master PDO in case none of the slave connections is available.
     * @return PDO the PDO instance for the currently active slave connection. Null is returned if no slave connection
     * is available and `$fallbackToMaster` is false.
     */
    public function getSlavePdo($fallbackToMaster = true) {
        $db = $this->getSlave(false);
        if ($db === null) {
            return $fallbackToMaster ? $this->getMasterPdo() : null;
        } else {
            return $db->pdo;
        }
    }

    /**
     * Returns the PDO instance for the currently active master connection.
     * This method will open the master DB connection and then return [[pdo]].
     * @return PDO the PDO instance for the currently active master connection.
     */
    public function getMasterPdo() {
        $this->open();
        return $this->pdo;
    }

    /**
     * Returns the currently active slave connection.
     * If this method is called the first time, it will try to open a slave connection when [[enableSlaves]] is true.
     * @param boolean $fallbackToMaster whether to return a master connection in case there is no slave connection available.
     * @return Connection the currently active slave connection. Null is returned if there is slave available and
     * `$fallbackToMaster` is false.
     */
    public function getSlave($fallbackToMaster = true) {
        if (!$this->enableSlaves) {
            return $fallbackToMaster ? $this : null;
        }

        if ($this->_slave === false) {
            $this->_slave = $this->openFromPool($this->slaves, $this->slaveConfig);
        }

        return $this->_slave === null && $fallbackToMaster ? $this : $this->_slave;
    }

    /**
     * Executes the provided callback by using the master connection.
     *
     * This method is provided so that you can temporarily force using the master connection to perform
     * DB operations even if they are read queries. For example,
     *
     * ```php
     * $result = $db->useMaster(function ($db) {
     *     return $db->createCommand('SELECT * FROM user LIMIT 1')->queryOne();
     * });
     * ```
     *
     * @param callable $callback a PHP callable to be executed by this method. Its signature is
     * `function (Connection $db)`. Its return value will be returned by this method.
     * @return mixed the return value of the callback
     */
    public function useMaster(callable $callback) {
        $enableSlave = $this->enableSlaves;
        $this->enableSlaves = false;
        $result = call_user_func($callback, $this);
        $this->enableSlaves = $enableSlave;
        return $result;
    }

    /**
     * Opens the connection to a server in the pool.
     * This method implements the load balancing among the given list of the servers.
     * @param array $pool the list of connection configurations in the server pool
     * @param array $sharedConfig the configuration common to those given in `$pool`.
     * @return Connection the opened DB connection, or null if no server is available
     * @throws InvalidConfigException if a configuration does not specify "dsn"
     */
    protected function openFromPool(array $pool, array $sharedConfig) {
        if (empty($pool)) {
            return null;
        }

        if (!isset($sharedConfig['class'])) {
            $sharedConfig['class'] = get_class($this);
        }

        $cache = is_string($this->serverStatusCache) ? Yii::$app->get($this->serverStatusCache, false) : $this->serverStatusCache;

        shuffle($pool);

        foreach ($pool as $config) {
            $config = array_merge($sharedConfig, $config);
            if (empty($config['dsn'])) {
                throw new InvalidConfigException('The "dsn" option must be specified.');
            }

            $key = [__METHOD__, $config['dsn']];
            if ($cache instanceof Cache && $cache->get($key)) {
                // should not try this dead server now
                continue;
            }

            /* @var $db Connection */
            $db = Yii::createObject($config);

            try {
                $db->open();
                return $db;
            } catch (\Exception $e) {
                Yii::warning("Connection ({$config['dsn']}) failed: " . $e->getMessage(), __METHOD__);
                if ($cache instanceof Cache) {
                    // mark this server as dead and only retry it after the specified interval
                    $cache->set($key, 1, $this->serverRetryInterval);
                }
            }
        }

        return null;
    }

    /**
     * Close the connection before serializing.
     * @return array
     */
    public function __sleep() {
        $this->close();
        return array_keys((array) $this);
    }

}
