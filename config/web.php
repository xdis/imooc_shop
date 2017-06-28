<?php

$params = require(__DIR__ . '/params.php');

$config = [
	'id'           => 'basic',
	'basePath'     => dirname(__DIR__),
	'language'     => 'zh-cn',
	'charset'      => 'utf-8',
	'bootstrap'    => ['log'],
	'defaultRoute' => 'index',
	'aliases'      => [
		'@doctorjason\mailerqueue' => '@vendor/doctorjason/mailerqueue/src',
	],
	'components'   => [
		//配置kafka model
		/*        'kafka'        => [
					'class'       => '\\app\\models\\Kafka',
					'broker_list' => 'localhost:9092',
					'topic'       => 'asynclog',
				],*/

		'elasticsearch' => [
			'class' => 'yii\elasticsearch\Connection',
			'nodes' => [
				['http_address' => '192.168.1.14:9200'],
				// configure more hosts if you have a cluster
			],
		],
		//权限管理
		'authManager'   => [
			'class' => 'yii\rbac\DbManager',

		],
		//redis缓存
		'redis'         => [
			'class'    => 'yii\redis\Connection',
			'hostname' => 'localhost',
			'port'     => 6379,
			'database' => 0,
		],
		//session redis缓存
		'session'       => [
			'class'     => 'yii\redis\Session',
			'redis'     => [
				'hostname' => 'localhost',
				'port'     => 6379,
				'database' => 3,
			],
			'keyPrefix' => 'redis_sess_',
		],
		//前端资源管理
		'assetManager'  => [
			'class'   => 'yii\web\AssetManager',
			//写需要压缩的文件
			'bundles' => [
				'yii\web\JqueryAsset'          => [
					'js' => [
						YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js',
					],
				],
				'yii\bootstrap\BootstrapAsset' => [
					'css' => [
						YII_ENV_DEV ? 'css/bootstrap.min.css' : 'css/bootstrap.min.css',
					],
				],

			],
		],

		//请求
		'request'       => [
			'cookieValidationKey' => 'q6ga0ArPuP1iWsey2H6aoeWsP7G98FnL',
		],
		//缓存
		'cache'         => [
			//  'class' => 'yii\caching\FileCache',
			'class' => 'yii\redis\Cache',
			'redis' => [
				'hostname' => 'localhost',
				'port'     => 6379,
				'database' => 2,
			],
		],
		//user组件
		'user'          => [
			'identityClass'   => 'app\models\User',
			'enableAutoLogin' => true,
			'idParam'         => '__user',
			'identityCookie'  => ['name' => '__user_identity', 'httpOnly' => true],
			'loginUrl'        => ['/member/auth'],
		],
		//后台admin组件
		'admin'         => [
			'class'           => 'yii\web\User',
			'identityClass'   => 'app\modules\models\Admin',
			'idParam'         => '__admin',
			'identityCookie'  => ['name' => '__admin_identity', 'httpOnly' => true],
			'enableAutoLogin' => true,
			'loginUrl'        => ['/admin/public/login'],
		],
/*		'errorHandler'  => [
			'errorAction' => 'index/error',
		],*/
		//邮件发送
		'mailer'        => [
			// 'class'            => 'yii\swiftmailer\Mailer',
			'class'            => 'doctorjason\mailerqueue\MailerQueue',
			// send all mails to a file by default. You have to set
			// 'useFileTransport' to false and configure a transport
			// for the mailer to send real emails.
			'db'               => 1,
			'useFileTransport' => false,
			'transport'        => [
				'class'      => 'Swift_SmtpTransport',
				'host'       => 'smtp.163.com',
				'username'   => '18380358053@163.com',
				'password'   => '739330062wangjie',
				'port'       => '465',
				'encryption' => 'ssl',
			],

		],
		//sentry日志收集
		'sentry'        => [
			'class'       => 'mito\sentry\SentryComponent',
			'dsn'         => 'https://1cabf1adf4ae46329af320bd03a03a63:f60571898c7e4537bb04ad50a8032804@sentry.io/165950',
			//  'publicDsn'  => 'https://1cabf1adf4ae46329af320bd03a03a63@sentry.io/165950',
			// private DSN
			'environment' => 'staging',
			// if not set, the default is `production`
			'jsNotifier'  => false,
			/*            // to collect JS errors. Default value is `false`
						'jsOptions'   => [ // raven-js config parameter
										   'whitelistUrls' => [ // collect JS errors from these urls
																//  'http://staging.my-product.com',
																//  'https://my-product.com',
										   ],
						],*/
		],
		//系统日志记录
		'log'           => [
			'targets' => [
				[
					'class'  => 'mito\sentry\SentryTarget',
					'levels' => ['error'],
					'except' => [
						'yii\web\HttpException:404',
					],
				],
				/*
								[
									'class'          => 'yii\log\FileTarget',
									'levels'         => ['info'],
									//'categories'     => ['testkafka'],
									'logVars'        => ['$_SERVER'],
									'exportInterval' => 1,
									'logFile'        => '@app/runtime/logs/Kafka.log',
								],*/
				/*                [
									'class'          => 'yii\log\FileTarget',
									'levels'         => ['info'],
									'categories'     => ['testkafka'],
									'logVars'        => [],
									'exportInterval' => 1,
									'logFile'        => '@app/runtime/logs/Kafka.log',
								],
								[
									'class'   => 'yii\log\FileTarget',
									'levels'  => ['error', 'warning'],
									'logFile' => '@app/runtime/log/shop/error.log',
								],
							],*/
			],
		],
		'db'            => require(__DIR__ . '/db.php'),
		//美化地址
		'urlManager'    => [
			'enablePrettyUrl' => true, //美化地址
			'showScriptName'  => false,//是否显示index.php
			'suffix'          => '.html', //后缀为.html
			//设置规则
			'rules'           => [
				//前台路由规则设置
				'<controller:(index|cart|order)>' => '<controller>/index',
				'auth'                            => 'member/auth',
				'product-<productid:\d+>'         => 'product/detail',

				'product-category-<cateid:\d+>'   => 'product/index',
				//后台路由规则  访问back 进入 /admin/default/index
				[
					'pattern' => 'back',
					'route'   => '/admin/default/index',
					'suffix'  => '.html',
				],
			],
		],


	],
	'params'       => $params,
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config[ 'bootstrap' ][]        = 'debug';
	$config[ 'modules' ][ 'debug' ] = [
		'class'      => 'yii\debug\Module',
		'allowedIPs' => ['*'],
	];

	$config[ 'bootstrap' ][]        = 'gii';
	$config[ 'modules' ][ 'gii' ]   = [
		'class'      => 'yii\gii\Module',
		'allowedIPs' => ['127.0.0.1'],
	];
	$config[ 'modules' ][ 'admin' ] = [
		'class' => 'app\modules\admin',
	];
}

return $config;
