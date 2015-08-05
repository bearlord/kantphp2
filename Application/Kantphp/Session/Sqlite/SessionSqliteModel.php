<?php

require_once KANT_PATH . 'Model/BaseModel.php';

class SessionSqliteModel extends BaseModel {

    protected $table = 'session';
    protected $primary = 'sessionid';
    private $_dbConfig;

    public function __construct() {
        $this->_setDbConfig();
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
        $this->db->dbTablepre = $this->_dbConfig[$this->adapter]['tablepre'];
        $this->db->table = $this->table;
        $this->db->primary = $this->primary;
    }

    private function _setDbConfig() {
        $this->_dbConfig = array(
            'default' => array(
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
            )
        );
    }

    /**
     * Delete all Session
     */
    public function deleteAll() {
        return $this->db->from($this->table)->delete();
    }

    /**
     * Delete Expired Session
     * @param type $expiretime
     */
    public function deleteExpire($expiretime) {
        $this->db->from($this->table)->whereLess('lastvisit', $expiretime)->delete();
    }

}

?>
