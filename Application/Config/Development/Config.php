<?php

return [
    'debug' => true,
    'token' => [
        'switch' => true,
    ],
    'database' => [
        //default configuration
        'default' => [
            'dsn' => 'mysql:host=localhost;dbname=kantphp',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'tablePrefix' => 'kant_'
        ],
    ]
];
?>
