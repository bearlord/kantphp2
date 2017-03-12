 <?php

return [
    'debug' => true,
    'token' => [
        'switch' => true,
    ],
    'database' => [
        //default configuration
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=kantphp',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'tablePrefix' => 'kant_'
        ],
        'pg' => [
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=kantphp',
            'username' => 'postgres',
            'password' => 'postgres',
            'charset' => 'utf8',
            'tablePrefix' => 'kant_'
        ]
    ],
    'session' => [
        'driver' => 'database',
        'table' => 'session',
        'cookie' => 'kant_session',
        'maxlifetime' => 1800,
    ],
];
?>
