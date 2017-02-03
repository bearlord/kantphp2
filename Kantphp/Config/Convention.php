<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
return [
    'check_app_dir' => true,
    'dir_secure_filename' => 'index.html',
    'dir_secure_content' => 'Powered By KantPHP Framework 2.1',
    'route' => [
        'module' => 'index',
        'ctrl' => 'index',
        'act' => 'index'
    ],
    'default_return_type' => 'html',
    'path_info_repair' => false,
    'debug' => true,
    'url_suffix' => '.html',
    'theme' => 'default',
    'template_suffix' => ".php",
    'action_suffix' => 'Action',
    'redirect_tpl' => 'dispatch/redirect',
    'lang' => 'zh_CN',
    'timezone' => 'Etc/GMT-8',
    'charset' => 'utf-8',
    'lock_ex' => '1',
    'db_fields_cache' => false,
    'cookie' => [
        'cookie_domain' => '',
        'cookie_path' => '/',
        'cookie_pre' => 'kantphp_',
        'cookie_ttl' => 0,
        'auth_key' => 'NMa1FcQBE1HHHd4AQyTV'
    ],
    'session' => [
        'default' => [
            'type' => 'original',
            'maxlifetime' => 1800,
        ],
        'file' => [
            'type' => 'file',
            'maxlifetime' => 1800,
            'auth_key' => 'NMa1FcQBE1HHHd4AQyTV',
        ],
        'sqlite' => [
            'type' => 'sqlite',
            'maxlifetime' => 1800,
            'auth_key' => 'NMa1FcQBE1HHHd4AQyTV',
        ]],
    'database' => [
        //default configuration
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=kantphp',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'tablePrefix' => 'kant_'
        ],
        //postgresql
        'pgsql' => [
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=mydatabase',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'tablePrefix' => 'kant_'
        ],
        //sqlite
        'sqlite' => [
            'dsn' => CACHE_PATH . 'SqliteDb/test.db',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'tablePrefix' => 'kant_'
        ]
    ],
    //cache config
    'cache' => [
        //default file cache type
        'file' => [
            'type' => 'file'
        ],
        //memcache type
        'memcache' => [
            'type' => 'memcache',
            'hostname' => 'localhost',
            'port' => 11211
        ],
        //redis cache type
        'redis' => [
            'type' => 'redis',
            'hostname' => '127.0.0.1',
            'port' => 6379
        ]
    ],
    'token' => [
        'switch' => false,
        'name' => '__hash__',
        'type' => 'md5',
        'reset' => false
    ],
    'tags' => [
        'app_begin' => [],
        'app_end' => [],
        'view_filter' => ['Kant\Behavior\TokenBuildBehavior']
    ]
];

