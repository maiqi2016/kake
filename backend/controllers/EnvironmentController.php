<?php

namespace backend\controllers;

use yii\helpers\Url;

/**
 * 项目环境管理
 *
 * @auth-inherit-except add edit front sort
 */
class EnvironmentController extends GeneralController
{
    /**
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'name' => [
                'title' => '标题',
                'min-width' => '100px'
            ],
            'value' => [
                'title' => '内容'
            ]
        ];
    }

    /**
     * 项目环境预览
     *
     * @auth-pass-all
     */
    public function actionIndex()
    {
        $env = [
            [
                'name' => '协议版本',
                'value' => $_SERVER['SERVER_PROTOCOL']
            ],
            [
                'name' => '网关版本',
                'value' => $_SERVER['GATEWAY_INTERFACE']
            ],
            [
                'name' => 'Web服务器',
                'value' => $_SERVER['SERVER_SOFTWARE']
            ],
            [
                'name' => '服务器IP',
                'value' => $_SERVER['SERVER_ADDR']
            ],
            [
                'name' => '服务器端口',
                'value' => $_SERVER['SERVER_PORT']
            ],
            [
                'name' => 'PHP版本',
                'value' => PHP_VERSION
            ],
            [
                'name' => 'Zend版本',
                'value' => Zend_Version()
            ],
            [
                'name' => 'MySQL版本',
                'value' => `mysql --version`
            ],
            [
                'name' => '服务器信息',
                'value' => php_uname()
            ],
            [
                'name' => '用户信息',
                'value' => $_SERVER['HTTP_USER_AGENT']
            ],
        ];

        return $this->showListDiy($env);
    }
}
