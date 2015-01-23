<?php
return [
	'name' => 'CARAT',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
		'mongodb' => [
			'class' => 'yii\mongodb\Connection',
			'dsn' => 'mongodb://localhost:27017/acra',
		],
		'redis' => [
			'class' => 'yii\redis\Connection',
			'hostname' => 'localhost',
			'port' => 6379,
			'database' => 0,
		],
        'cache' => [
			'class' => 'yii\caching\MemCache',
			'servers' => [
				[
					'host' => 'localhost',
					'port' => 11211,
				],
			],
        ],
    ],
];
