<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'Acra API',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
	'defaultRoute' => 'api/send',
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
		'request' => [
			'enableCsrfValidation' => false,
		],
		'response' => [
			'format' => \yii\web\Response::FORMAT_JSON,
		],
		'user' => [
			'identityClass' => 'common\models\User',
		],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
					'logFile' => '@runtime/logs/api.log',
                    'levels' => ['error', 'warning'],
					'logVars' => [],
                ],
            ],
        ],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'send' => 'api/send',
				'api/sendApple' => 'api/send-apple',
			],
		],
        'errorHandler' => [
			'class' => 'frontend\components\ErrorHandler',
        ],
    ],
    'params' => $params,
];
