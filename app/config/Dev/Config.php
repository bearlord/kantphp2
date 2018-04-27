<?php

return [
	'debug' => true,
	'token' => [
		'switch' => true,
	],
	'session' => [
		'driver' => 'file',
		'table' => 'session',
		'cookie' => 'kant_session',
		'maxlifetime' => 1800,
	],
	'filesystems' => [
		'default' => 'public'
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
		]
	]
];
?>
