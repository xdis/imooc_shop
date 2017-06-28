<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests/codeception');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',

    'aliases' => [
        '@doctorjason/mailerqueue' => '@vendor/doctorjason/mailerqueue/src'
    ],
    'components' => [
        'kafka'       => [
            'class'       => '\\app\\models\\Kafka',
            'broker_list' => 'localhost:9092',
            'topic'       => 'asynclog',
        ],
        //邮件发送
        'mailer'       => [
            // 'class'            => 'yii\swiftmailer\Mailer',
            'class' => 'doctorjason\mailerqueue\MailerQueue',
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
        'redis'        => [
            'class'    => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port'     => 6379,
            'database' => 0,
        ],
        //权限管理
        'authManager'  => [
            'class'     => 'yii\rbac\DbManager',

        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['testkafka'],
                    'logVars' => [],
                    'exportInterval' => 1,
                    'logFile' => '@app/runtime/logs/Kafka.log',
                ]
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
