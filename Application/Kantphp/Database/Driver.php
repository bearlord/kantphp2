<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Database;

use Kant\Exception\KantException;
use Kant\KantFactory;
use InvalidArgumentException;

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
    protected $dbo;

    /**
     *
     * Get instantce of the final object
     *
     * @param string $config
     * @return object on success
     */
    public static function connect($config = '') {
        $name = md5(serialize($config));
        if (self::$_database[$name] == '') {
            self::$_database[$name] = (new self())->connect($config);
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
    public function connectInternal($config = "") {
        $options = $this->parseConfig($config);
        $dbType = $options['type'];
        if (empty($dbType)) {
            throw new InvalidArgumentException('Underfined db type');
        }
        $class = "Kant\\Database\\Driver\\" . ucfirst($dbType);
        if (!class_exists($class)) {
            throw new KantException(sprintf('Unable to load Database Driver: %s', $options['type']));
        }
        $this->dbo = new $class;
        $this->dbo->open($options);
        $this->dbo->dbTablepre = $options['tablepre'];
        return $this->dbo;
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
     * @param array $config
     */
    protected function parseDsn($str) {
        $info = parse_url($str);
        if (!$info) {
            return [];
        }
        $dsn = [
            'type'     => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'hostname' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '',
            'database' => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset'  => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];

        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dsn['params'] = [];
        }
        return $dsn;
    }

    /**
     *
     * Close database connection
     *
     */
    protected function close() {
        $this->dbo->close();
    }

    /**
     *
     * Destruct
     */
    public function __destruct() {
        $this->close();
    }

}
