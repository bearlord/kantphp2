<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database\Mysql;

use Kant\Exception\KantException;
use PDO;

/**
 * Mysql database
 * 
 * @access public
 * @since version 1.1
 * 
 */
class Connection extends \Kant\Database\Connection{

    public $options;
    
    public function __construct($options = []) {
        $this->options = $options;
        $this->connect();
    }

    /**
     *
     * Creates a PDO object and connects to the database.
     *
     * @return void
     */
    public function connect() {    
        // check for PDO extension
        if (!extension_loaded('pdo')) {
            throw new KantException('The PDO extension is required for this adapter but the extension is not loaded');
        }
        // check for PDO_PGSQL extension
        if (!extension_loaded('pdo_mysql')) {
            throw new KantException('The PDO_MYSQL extension is required for this adapter but the extension is not loaded');
        }
        $dsn = sprintf("mysql:host=%s;dbname=%s", $this->options['hostname'], $this->options['database']);
        //Request a persistent connection, rather than creating a new connection.
        if (isset($this->options['persistent']) && $this->options['persistent'] == true) {
            $extraoptions[PDO::ATTR_PERSISTENT] = true;
        } else {
            $extraoptions = [];
        }
//        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8"; 
        try {
            $pdo = new PDO($dsn, $this->options['username'], $this->options['password'], $extraoptions);
            // always use exceptions.
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new KantException(sprintf('Can not connect to MySQL server or cannot use database.%s', $e->getMessage()));
        }
        $pdo->exec(sprintf("SET NAMES \"%s\"", $this->options['charset']));
        return $pdo;
    }  

}
