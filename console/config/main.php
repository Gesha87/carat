<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'Acra Console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
	'controllerMap' => [
		'mongodb-migrate' => 'yii\mongodb\console\controllers\MigrateController'
	],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
					'logVars' => [],
                ],
				[
					'class' => 'yii\log\FileTarget',
					'logFile' => '@runtime/logs/run.log',
					'levels' => ['info'],
					'categories' => ['acra'],
					'logVars' => [],
				],
            ],
        ],
    ],
    'params' => $params,
];
