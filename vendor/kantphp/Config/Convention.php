<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
return [
    /**
     * |--------------------------------------------------------------------------
     * | App language
     * |--------------------------------------------------------------------------
     */
    'language' => 'zh-CN',
    /**
     * |--------------------------------------------------------------------------
     * | Time zone
     * |--------------------------------------------------------------------------
     */
    'timezone' => 'Etc/GMT-8',
    /**
     * |--------------------------------------------------------------------------
     * | Additon components autoloaded
     * |--------------------------------------------------------------------------
     */
    'components' => [
        'log' => [
            'traceLevel' => 3,
            'targets' => [
                [
                    'class' => 'Kant\Log\FileTarget',
                    'levels' => [
                        'error',
                        'warning',
                        'trace',
                        'info'
                    ]
                ]
            ]
        ],
        'view' => [
            'theme' => 'default',
            'ext' => '.php'
        ],
        'cookie' => [
            'domain' => '',
            'path' => '/',
            'prefix' => 'kant_',
            'expire' => 0,
            'key' => 'NMa1FcQBE1HHHd4AQyTV',
            'secure' => false
        ],
        'files' => [
            /*
             * |--------------------------------------------------------------------------
             * | Default Filesystem Disk
             * |--------------------------------------------------------------------------
             * |
             * | Here you may specify the default filesystem disk that should be used
             * | by the framework. The "local" disk, as well as a variety of cloud
             * | based disks are available to your application. Just store away!
             * |
             */

            'default' => 'local',
            /*
              |--------------------------------------------------------------------------
              | Default Cloud Filesystem Disk
              |--------------------------------------------------------------------------
              |
              | Many applications store files both locally and in the cloud. For this
              | reason, you may specify a default "cloud" driver here. This driver
              | will be bound as the Cloud disk implementation in the container.
              |
             */
            'cloud' => 's3',
            /*
              |--------------------------------------------------------------------------
              | Filesystem Disks
              |--------------------------------------------------------------------------
              |
              | Here you may configure as many filesystem "disks" as you wish, and you
              | may even configure multiple disks of the same driver. Defaults have
              | been setup for each driver as an example of the required options.
              |
              | Supported Drivers: "local", "ftp", "s3", "rackspace"
              |
             */
            'disks' => [
                'local' => [
                    'driver' => 'local',
                    'root' => APP_PATH . '/storage'
                ],
                'public' => [
                    'driver' => 'local',
                    'root' => PUBLIC_PATH . 'storage',
                    'url' => APP_URL . 'storage',
                    'visibility' => 'public'
                ],
                's3' => [
                    'driver' => 's3',
                    'key' => '',
                    'secret' => '',
                    'region' => '',
                    'bucket' => ''
                ]
            ]
        ]
    ],
    'charset' => 'utf-8',

    'session' => [
        /**
         * |--------------------------------------------------------------------------
         * | Default Session Driver
         * |--------------------------------------------------------------------------
         * |
         * | This option controls the default session "driver" that will be used on
         * | requests. By default, we will use the lightweight native driver but
         * | you may specify any of the other wonderful drivers provided here.
         * |
         * | Supported: "file", "cookie", "database", "apc",
         * | "memcached", "redis", "array"
         * |
         */
        'driver' => null,
        /**
         * |--------------------------------------------------------------------------
         * | Session Lifetime
         * |--------------------------------------------------------------------------
         * |
         * | Here you may specify the number of minutes that you wish the session
         * | to be allowed to remain idle before it expires. If you want them
         * | to immediately expire on the browser closing, set that option.
         * |
         */
        'lifetime' => 120,
        'expire_on_close' => false,
        /**
         * |--------------------------------------------------------------------------
         * | Session Encryption
         * |--------------------------------------------------------------------------
         * |
         * | This option allows you to easily specify that all of your session data
         * | should be encrypted before it is stored. All encryption will be run
         * | automatically by Laravel and you can use the Session like normal.
         * |
         */
        'encrypt' => false,
        /**
         * |--------------------------------------------------------------------------
         * | Session File Location
         * |--------------------------------------------------------------------------
         * |
         * | When using the native session driver, we need a location where session
         * | files may be stored. A default has been set for you but a different
         * | location may be specified. This is only needed for file sessions.
         * |
         */
        'files' => '@session_path/',
        /**
         * |--------------------------------------------------------------------------
         * | Session Database Connection
         * |--------------------------------------------------------------------------
         * |
         * | When using the "database" or "redis" session drivers, you may specify a
         * | connection that should be used to manage these sessions. This should
         * | correspond to a connection in your database configuration options.
         * |
         */
        'connection' => null,
        /*
          |--------------------------------------------------------------------------
          | Session Database Table
          |--------------------------------------------------------------------------
          |
          | When using the "database" session driver, you may specify the table we
          | should use to manage the sessions. Of course, a sensible default is
          | provided for you; however, you are free to change this as needed.
          |
         */
        'table' => 'sessions',
        /*
          |--------------------------------------------------------------------------
          | Session Sweeping Lottery
          |--------------------------------------------------------------------------
          |
          | Some session drivers must manually sweep their storage location to get
          | rid of old sessions from storage. Here are the chances that it will
          | happen on a given request. By default, the odds are 2 out of 100.
          |
         */
        'lottery' => [
            2,
            100
        ],
        /*
          |--------------------------------------------------------------------------
          | Session Cookie Name
          |--------------------------------------------------------------------------
          |
          | Here you may change the name of the cookie used to identify a session
          | instance by ID. The name specified here will get used every time a
          | new session cookie is created by the framework for every driver.
          |
         */
        'cookie' => 'kant_session',
        /*
          |--------------------------------------------------------------------------
          | Session Cookie Path
          |--------------------------------------------------------------------------
          |
          | The session cookie path determines the path for which the cookie will
          | be regarded as available. Typically, this will be the root path of
          | your application but you are free to change this when necessary.
          |
         */
        'path' => '/',
        /*
          |--------------------------------------------------------------------------
          | Session Cookie Domain
          |--------------------------------------------------------------------------
          |
          | Here you may change the domain of the cookie used to identify a session
          | in your application. This will determine which domains the cookie is
          | available to in your application. A sensible default has been set.
          |
         */
        'domain' => null,
        /*
          |--------------------------------------------------------------------------
          | HTTPS Only Cookies
          |--------------------------------------------------------------------------
          |
          | By setting this option to true, session cookies will only be sent back
          | to the server if the browser has a HTTPS connection. This will keep
          | the cookie from being sent to you if it can not be done securely.
          |
         */
        'secure' => false,
        /*
          |--------------------------------------------------------------------------
          | HTTP Access Only
          |--------------------------------------------------------------------------
          |
          | Setting this value to true will prevent JavaScript from accessing the
          | value of the cookie and the cookie will only be accessible through
          | the HTTP protocol. You are free to modify this option if needed.
          |
         */
        'http_only' => true
    ],


];

