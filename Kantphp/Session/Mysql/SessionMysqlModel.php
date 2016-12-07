<?php

namespace Kant\Session\Mysql;

use Kant\Exception\KantException;
use PDO;

class SessionMysqlModel {

    protected $table = 'session';
    protected $primary = 'sessionid';
    protected $db;
    protected $_dbConfig = array();

    public function __construct() {
        $this->_dbConfig = $this->_setDbConfig();
        if ($this->db == '') {
            $this->db = new MysqlDatabase($this->_dbConfig);
        }
        $this->table = $this->_dbConfig['tablepre'] . $this->table;
    }

    private function _setDbConfig() {
        return array(
            'hostname' => 'localhost',
            'port' => '3306',
            'database' => '4kmovie',
            'username' => 'root',
            'password' => 'root',
            'tablepre' => 'kant_',
            'charset' => 'utf8',
            'type' => 'mysql',
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
        if ($row) {
            return $row;
        }
    }

    public function saveSession($data, $sessionid = '') {
        if ($sessionid == '') {
            $sql = sprintf("INSERT INTO %s (sessionid, data, lastvisit, ip, http_cookie) VALUES ('%s', '%s', '%s', '%s', '%s')", $this->table, $data['sessionid'], $data['data'], $data['lastvisit'], $data['ip'], $data['http_cookie']);
        } else {
            $sql = sprintf("UPDATE %s SET data = '%s', lastvisit = '%s', ip = '%s', http_cookie = '%s' WHERE sessionid = '%s'", $this->table, $data['data'], $data['lastvisit'], $data['ip'], $data['http_cookie'], $sessionid);
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

class MysqlDatabase {

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
        if (!extension_loaded('pdo_mysql')) {
            throw new KantException('The PDO_MYSQL extension is required for this adapter but the extension is not loaded');
        }

        $dsn = sprintf("mysql:host=%s;dbname=%s", $this->config['hostname'], $this->config['database']);
        //Request a persistent connection, rather than creating a new connection.
        if (isset($this->config['persistent']) && $this->config['persistent'] == true) {
            $options = array(PDO::ATTR_PERSISTENT => true);
        } else {
            $options = null;
        }
//        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8"; 
        try {
            $this->dbh = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            // always use exceptions.
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new KantException(sprintf('Can not connect to MySQL server or cannot use database.%s', $e->getMessage()));
        }
        $this->dbh->exec(sprintf("SET NAMES \"%s\"", $this->config['charset']));
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
