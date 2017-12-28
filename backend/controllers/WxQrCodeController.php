<?php

namespace backend\controllers;

use Oil\src\Helper;
use Yii;
use yii\helpers\Url;

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
        2 => '分销商推广',
        3 => '分销商活动推广',
    ];

    /**
     * @var array 功能描述
     */
    public static $typeDescription = [
        1 => [
            'A. 用户扫描后出现关注界面(如果用户未关注)；',
            'B. 随后跳转到对话框。'
        ],
        2 => [
            'A. 用户扫描后出现关注界面(如果用户未关注)；',
            'B. 随后跳转到对话框，并弹出欢迎关注致辞和注册分销商的入口链接；',
            'C. 微信后台创建同名的用户分组；',
            'D. 并在微信后台将该分销商加入到该分组中。'
        ],
        3 => [
            'A. 用户扫描后出现关注界面(如果用户未关注)；',
            'B. 随后跳转到对话框，并弹出欢迎关注致辞和分销商活动的入口链接；'
        ],
    ];

    /**
     * @var array 是否需要分组
     */
    public static $needGroup = [
        1 => false,
        2 => true,
        3 => true
    ];

    /**
     * 是否需要回复
     *
     * @return array
     */
    public function needReply()
    {
        $url = SCHEME . Yii::$app->params['frontend_url'];

        return [
            1 => false,
            2 => "欢迎加入喀客，<a href='" . $url . Url::toRoute(['producer/apply-distributor']) . "'>点击这里注册</a>分销商",
            3 => "欢迎加入喀客，<a href='" . $url . Url::toRoute([
                    'distribution/item',
                    'channel' => 'nubXnej7',
                    'tip' => 'yes'
                ]) . "'>点击这里</a>参加抽奖活动"
        ];
    }

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
        $assist = [
            'type' => [
                'title' => '类型',
                'elem' => 'select',
                'value' => 1,
                'list' => self::$type
            ]
        ];

        foreach (self::$typeDescription as $key => $info) {
            $assist['description_' . $key] = [
                'title' => '说明',
                'elem' => 'text',
                'class' => 'bg-default',
                'value' => is_array($info) ? implode('<br>', $info) : $info,
                'label' => 5,
                'html' => true,
                'tag' => 'pre',
                'show' => ['type' => 'value == ' . $key]
            ];
        }

        $assist['name'] = [
            'title' => '分组名称',
            'elem' => 'textarea',
            'label' => 6,
            'row' => 10,
            'placeholder' => "自动使用该名称创建组名，多个请换行如：\n\n迈骐国际旅行社\n喀客酒店预订"
        ];

        return $assist;
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
        $qrCode = Yii::$app->oil->wx->qrcode;

        // 批量
        if (count($names) > 1) {
            foreach ($names as $name) {
                $name = trim($name);
                $url = $qrCode->url($qrCode->forever($params['type'] . '.' . $name)->ticket);
                $filename = $directoryName . DS . self::$type[$params['type']] . '_' . $name . '.png';

                parent::getPathByUrl($url, null, $filename);
            }

            Helper::archiveDirectory(Yii::$app->params['tmp_path'] . DS . $directoryName);
            Yii::$app->oil->download->download($path . '.zip', '批量生成的二维码.zip');

            @unlink($path . '.zip');

        } else { // 单个

            $name = $names[0];
            $url = $qrCode->url($qrCode->forever($params['type'] . '.' . $name)->ticket);
            $filename = self::$type[$params['type']] . '_' . $name . '.png';

            Yii::$app->oil->download->remoteDownload($url, $filename);
        }
    }
}
