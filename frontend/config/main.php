<?php
return [
    'id' => 'frontend',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'controllerNamespace' => 'frontend\controllers',
    'bootstrap' => ['log'],
    'params' => array_merge(
        require(__DIR__ . '/../../common/config/params-local.php'),
        require(__DIR__ . '/../../common/config/params.php'),
        require(__DIR__ . '/params-local.php'),
        require(__DIR__ . '/params.php')
    ),
    'language'=>'zh-CN',
    'components' => [
        'session' => [
            // 'class'=>'yii\redis\Session',
            // 'keyPrefix' => 'sess_kake_frontend_',
            'name' => 'KK_SESS',
            'cookieParams' => [
                'domain' => DOMAIN,
                'lifetime' => 30 * 86400,
                'httpOnly' => true,
                'path' => '/',
            ],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@frontend/messages',
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'general/error',
        ],
    ],
];
