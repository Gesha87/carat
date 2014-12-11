<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'Acra Backend',
    'basePath' => dirname(__DIR__),
	'bootstrap' => ['log', 'localeUrls'],
	'defaultRoute' => 'site/index',
    'controllerNamespace' => 'backend\controllers',
	'sourceLanguage' => 'en_US',
	'language' => 'en',
    'components' => [
		'request' => [
			'enableCsrfValidation' => false,
		],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                'errors' => [
                    'class' => 'yii\log\FileTarget',
					'logFile' => '@runtime/logs/dashboard.log',
                    'levels' => ['error', 'warning'],
					'logVars' => [],
                ],
            ],
        ],
		'localeUrls' => [
			'class' => 'backend\components\LocaleUrls',
			'languages' => ['en', 'ru'],
			'languageCookieName' => 'lang',
			'enableDefaultSuffix' => true,
		],
		'i18n' => [
			'translations' => [
				'app*' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@app/messages',
				],
			],
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'class'=>'backend\components\UrlManager',
			'languageParam' => 'language',
			'rules' => [
				'<action:\w+>' => 'site/<action>',
			],
		],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'params' => $params,
];
