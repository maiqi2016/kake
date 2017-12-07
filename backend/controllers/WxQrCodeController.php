<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 公众号二维码管理
 *
 * @auth-inherit-except add front sort
 */
class WxQrCodeController extends GeneralController
{
    /**
     * @var array 二维码类型
     */
    public static $type = [
        1 => '关注推广',
        2 => '分销商推广'
    ];

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
            'type' => [
                'title' => '类型',
                'elem' => 'select',
                'value' => 1,
                'list' => self::$type
            ],
            'name' => [
                'title' => '分组名称',
                'elem' => 'textarea',
                'label' => 6,
                'row' => 10,
                'placeholder' => "自动使用该名称创建组名，多个请换行如：\n\n迈骐国际旅行社\n喀客酒店预订"
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function pageDocument()
    {
        return [
            'index' => [
                'title_icon' => 'qrcode',
                'title_info' => '二维码生成器',
                'button_info' => '生成二维码并下载',
                'action' => 'create'
            ]
        ];
    }

    /**
     * 公众号二维码生成器
     *
     * @auth-same {ctrl}/create
     */
    public function actionIndex()
    {
        return $this->showForm();
    }

    /**
     * 公众号二维码生成器
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->post();

        if (empty($params['name'])) {
            $this->goReference($this->getControllerName('index'), [
                'danger' => '请填写分组名称'
            ]);
        }

        $directoryName = 'wx_qr_code';
        $path = Yii::$app->params['tmp_path'] . DS . $directoryName;

        Helper::removeDirectory($path, false);

        $names = explode(PHP_EOL, $params['name']);
        $qrCode = Yii::$app->wx->qrcode;

        // 批量
        if (count($names) > 1) {
            foreach ($names as $name) {
                $name = trim($name);
                $url = $qrCode->url($qrCode->forever($params['type'] . '.' . $name)->ticket);
                $filename = $directoryName . DS . self::$type[$params['type']] . '_' . $name . '.png';

                parent::getPathByUrl($url, null, $filename);
            }

            Helper::archiveDirectory(Yii::$app->params['tmp_path'] . DS . $directoryName);
            Yii::$app->download->download($path . '.zip', '批量生成的二维码.zip');

            @unlink($path . '.zip');

        } else { // 单个

            $name = $names[0];
            $url = $qrCode->url($qrCode->forever($params['type'] . '.' . $name)->ticket);
            $filename = self::$type[$params['type']] . '_' . $name . '.png';

            Yii::$app->download->remoteDownload($url, $filename);
        }
    }
}
