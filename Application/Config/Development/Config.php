<?php

return [
    'debug' => true,
    'token' => [
        'switch' => true,
    ],
    'database' => [
        //default configuration
        'default' => [
            'hostname' => 'localhost',
            'port' => '3306',
            'database' => 'kantphp',
            'username' => 'root',
            'password' => '123456',
            'tablepre' => 'kant_',
            'charset' => 'utf8',
            'type' => 'mysql',
            'persistent' => 0,
            'autoconnect' => 1
        ],
    ]
];
?>
