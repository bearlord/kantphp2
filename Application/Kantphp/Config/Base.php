<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2015 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
!defined('IN_KANT') && exit('Access Denied');
return array(
    'route' => array(
        'module' => 'index',
        'ctrl' => 'index',
        'act' => 'index',
        'data' => array(
            'GET' => array()
        )
    ),
    'route_rules' => array(),
    'path_info_repair' => false,
    'debug' => false,
    'url_suffix' => '.html',
    'theme' => 'default',
    'template_suffix' => ".php",
    'action_suffix' => 'Action',
    'redirect_tpl' => 'dispatch/redirect',
    'lang' => 'zh_CN',
    'default_timezone' => 'Etc/GMT-8',
    'charset' => 'utf-8',
    'lock_ex' => '1',
    'cookie' => array(
        'cookie_domain' => '',
        'cookie_path' => '/',
        'cookie_pre' => 'kantphp_',
        'cookie_ttl' => 0,
        'auth_key' => 'NMa1FcQBE1HHHd4AQyTV'
    ),
    'session' => array(
        'default' => array(
            'type' => 'original',
            'maxlifetime' => 1800,
        ),
        'file' => array(
            'type' => 'file',
            'maxlifetime' => 1800,
            'auth_key' => 'NMa1FcQBE1HHHd4AQyTV',
        ),
        'sqlite' => array(
            'type' => 'sqlite',
            'maxlifetime' => 1800,
            'auth_key' => 'NMa1FcQBE1HHHd4AQyTV',
        )),
    'database' => array(
        //default configuration
        'default' => array(
            'hostname' => 'localhost',
            'port' => '3306',
            'database' => 'kantphp',
            'username' => 'root',
            'password' => 'root',
            'tablepre' => 'kant_',
            'charset' => 'utf8',
            'type' => 'mysql',
            'persistent' => 0,
            'autoconnect' => 1
        ),
        //openshift
        'openshift' => array(
            'hostname' => getenv('OPENSHIFT_MYSQL_DB_HOST'),
            'port' => getenv('OPENSHIFT_MYSQL_DB_PORT'),
            'database' => 'mzqltbruzaqbrsxpalti',
            'username' => getenv('OPENSHIFT_MYSQL_DB_USERNAME'),
            'password' => getenv('OPENSHIFT_MYSQL_DB_PASSWORD'),
            'tablepre' => 'kant_',
            'charset' => 'utf8',
            'type' => 'mysql',
            'persistent' => 0,
            'autoconnect' => 1
        ),
        //postgresql
        'pgsql' => array(
            'hostname' => 'localhost',
            'port' => '5432',
            'database' => 'bbs',
            'username' => 'root',
            'password' => 'root',
            'tablepre' => 'bbs_',
            'charset' => 'UTF-8',
            'type' => 'pdo_pgsql',
            'persistent' => 0,
            'autoconnect' => 1
        ),
        //sqlite
        'sqlite' => array(
            'hostname' => '',
            'port' => '',
            'database' => CACHE_PATH . 'SqliteDb/test.db',
            'username' => '',
            'password' => '',
            'tablepre' => 'test_',
            'charset' => 'UTF-8',
            'type' => 'pdo_sqlite',
            'persistent' => 0,
            'autoconnect' => 1
        )
    ),
    //cache config
    'cache' => array(
        //default file cache type
        'defalut' => array(
            'type' => 'file'
        ),
        //memcache type
        'memcache' => array(
            'type' => 'memcache',
            'hostname' => 'localhost',
            'port' => 11211
        ),
        //redis cache type
        'redis' => array(
            'type' => 'redis',
            'hostname' => '127.0.0.1',
            'port' => 6379
        )
    ),
    'tags' => array(
        'app_begin' => array(
        ),
        'app_end' => array(
        ),
        'path_info' => array(),
        'action_begin' => array(),
        'action_end' => array(),
        'view_begin' => array(),
        'view_parse' => array(
        ),
        'view_end' => array(),
    )
);

