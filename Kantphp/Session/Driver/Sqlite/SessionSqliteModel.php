<?php

namespace Kant\Session\Driver\Sqlite;

use Kant\Exception\KantException;
use PDO;

class SessionSqliteModel {

    protected $adapter = 'session_db';
    protected $table = 'session';
    protected $primary = 'sessionid';
    protected $db;
    protected $_dbConfig = array();

    public function __construct() {
        $this->_dbConfig = $this->_setDbConfig();
        if ($this->db == '') {
            $this->db = new SqliteDatabase($this->_dbConfig);
        }
        $this->table = $this->_dbConfig['tablepre'] . $this->table;
    }

    private function _setDbConfig() {
        return array(
            'hostname' => '',
            'port' => '',
            'database' => CACHE_PATH . 'Session/SessionSqlite/session.db',
            'username' => '',
            'password' => '',
            'tablepre' => 'kant_',
            'charset' => 'UTF-8',
            'type' => 'pdo_sqlite',
            'debug' => true,
            'persistent' => 0,
            'autoconnect' => 1
        );
    }

    /**
     * Read session
     * 
     * @param string $sessionid
     * @return 
     */
    public function readSession($sessionid) {
        $sql = sprintf("SELECT * FROM %s WHERE sessionid = '{$sessionid}'", $this->table);
        $row = $this->db->query($sql);
        return $row;
    }

    public function saveSession($data, $sessionid = '') {
        if ($sessionid == '') {
            $sql = sprintf("INSERT INTO %s (sessionid, data, lastvisit, ip) VALUES ('%s', '%s', '%s', '%s')", $this->table, $data['sessionid'], $data['data'], $data['lastvisit'], $data['ip']);
        } else {
            $sql = sprintf("UPDATE %s SET data = '%s', lastvisit = '%s', ip = '%s' WHERE sessionid = '%s'", $this->table, $data['data'], $data['lastvisit'], $data['ip'], $sessionid);
        }
        $row = $this->db->execute($sql);
        return $row;
    }

    /**
     * Delete session
     * 
     * @param string $sessionid
     * @return 
     */
    public function deleteSesssion($sessionid) {
        $sql = sprintf("DELETE FROM %s", $this->table);
        $row = $this->db->execute($sql);
        return $row;
    }

    /**
     * Delete all Session
     */
    public function deleteAll() {
        $sql = sprintf("DELETE FROM %s", $this->table);
        $row = $this->db->execute($sql);
        return $row;
    }

    /**
     * Delete Expired Session
     * @param type $expiretime
     */
    public function deleteExpire($expiretime) {
        $sql = sprintf("DELETE FROM %s WHERE lastvisit < $expiretime", $this->table);
        $row = $this->db->execute($sql);
        return $row;
    }

}

class SqliteDatabase {

    protected $dbh;

    public function __construct($config) {
        $this->open($config);
    }

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
        if (!extension_loaded('pdo_sqlite')) {
            throw new KantException('The PDO_SQLITE extension is required for this adapter but the extension is not loaded');
        }

        $dsn = sprintf("%s:%s", "sqlite", $this->config['database']);

        //Request a persistent connection, rather than creating a new connection.
        if (isset($this->config['persistent']) && $this->config['persistent'] == true) {
            $options = array(PDO::ATTR_PERSISTENT => true);
        } else {
            $options = null;
        }
        $this->config['username'] = null;
        $this->config['password'] = null;
        try {
            $this->dbh = new PDO($dsn, null, null, $options);
            // always use exceptions.
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new KantException(sprintf('Can not connect to SQLite server or cannot use database.%s', $e->getMessage()));
        }
//         $this->dbh->exec(sprintf("SET NAMES \"%s\"", $this->config['charset']));
        $this->database = $this->config['database'];
    }

    /**
     * SQl query
     * @param type $sql
     */
    public function query($sql) {
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * Execute sql
     * @param type $sql
     */
    public function execute($sql) {
        $sth = $this->dbh->prepare($sql);
        $row = $sth->execute();
        return $row;
    }

}

?>
