<?php

namespace backend\controllers;

/**
 * 短信验证码管理
 *
 * @auth-inherit-except add edit front sort
 */
class PhoneCaptchaController extends GeneralController
{
    // 模型
    public static $modelName = 'PhoneCaptcha';

    // 模型描述
    public static $modelInfo = '短信验证码';

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
    public static function indexFilter()
    {
        return [
            'phone' => 'input',
            'type' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'update_time' => [
                'elem' => 'input',
                'type' => 'date',
                'between' => true
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'update_time'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'phone',
            'captcha' => 'code',
            'type' => [
                'code',
                'info'
            ],
            'update_time',
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ],
        ];
    }
}
