<?php

return [
	'basePath' => dirname(dirname(__DIR__)),
	'session' => [
		'driver' => 'file',
		'table' => 'session',
		'cookie' => 'kant_session',
		'maxlifetime' => 1800,
	],
	'components' => [
		'db' => [
			'class' => 'Kant\Database\Connection',
			'dsn' => 'mysql:host=localhost;dbname=kantphp',
			'username' => 'root',
			'password' => '123456',
			'charset' => 'utf8',
			'tablePrefix' => 'p_'
		],
		'cache' => [
			'class' => 'Kant\Caching\FileCache',
		],
        'files' => [
            'default' => 'public'
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
	]
];
?>
