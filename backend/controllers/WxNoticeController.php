<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 公众号模板消息管理
 *
 * @auth-inherit-except add front sort
 */
class WxNoticeController extends GeneralController
{
    // 模板
    public static $tpl;

    // 模板格式成 json 字符串
    public static $tplFieldJson;

    /**
     * @inheritDoc
     */
    public function indexInitScript()
    {
        return ['$.createTplMsgField(' . self::$tplFieldJson . ');'];
    }

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        $assist = [
            'tpl' => [
                'title' => '模板',
                'elem' => 'select',
                'list' => array_column(self::$tpl, 'title', 'template_id'),
                'change' => '$.createTplMsgField(' . self::$tplFieldJson . ');'
            ],
            'user_group' => [
                'title' => '推送用户群体',
                'elem' => 'select',
                'value' => 1,
                'list' => [
                    1 => '所有用户',
                    2 => '管理员',
                    3 => '指定用户ID'
                ]
            ],
            'user_ids' => [
                'title' => '指定用户ID',
                'placeholder' => '多个用户ID用半角逗号隔开',
                'show' => ['user_group' => 'value == 3'],
                'label' => 5
            ]
        ];

        $split = PHP_EOL . PHP_EOL . str_repeat('-*-', 15) . PHP_EOL . PHP_EOL;

        foreach (self::$tpl as $item) {
            $assist[$item['template_id']] = [
                'title' => '样例',
                'elem' => 'text',
                'class' => 'bg-primary',
                'value' => $item['content'] . $split . $item['example'],
                'label' => 5,
                'html' => true,
                'tag' => 'pre',
                'show' => ['tpl' => 'value == "' . $item['template_id'] . '"']
            ];
        }

        $assist = array_merge($assist, [
            'header' => [
                'title' => '消息简介',
                'placeholder' => 'first，类似副标题，可留空',
                'label' => 5
            ],
            'footer' => [
                'title' => '消息备注',
                'placeholder' => 'remark，备注说明，可留空',
                'elem' => 'textarea',
                'label' => 5
            ],
            'url' => [
                'title' => '跳转地址',
                'placeholder' => '消息跳转地址，可留空',
                'label' => 5
            ]
        ]);

        return $assist;
    }

    /**
     * @inheritDoc
     */
    public function pageDocument()
    {
        return [
            'index' => [
                'title_icon' => 'send',
                'title_info' => '群发模板消息',
                'button_info' => '确认发送',
                'action' => 'send'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        list(self::$tpl, $field) = Yii::$app->wx->getMsgTpl();
        self::$tplFieldJson = json_encode($field, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 公众号消息模板预览
     *
     * @auth-same wx-menu/send
     */
    public function actionIndex()
    {
        return $this->showForm();
    }

    /**
     * 公众号模板消息发送
     */
    public function actionSend()
    {
        $params = Yii::$app->request->post();
        $field = Helper::popOne($params, 'field');

        foreach ($field as $key => $item) {
            if (empty($item)) {
                $this->goReference($this->getControllerName('index'), [
                    'danger' => '[' . $key . '] 模板字段不能为空',
                    'list' => $params
                ]);
            }
        }

        // 发送群体
        switch ($params['user_group']) {

            case 1 :
                $user = $this->listUser(null, 'openid');
                break;

            case 2 :
                $user = $this->listUser([
                    ['manager' => 1],
                    ['role' => 1]
                ], 'openid');
                break;

            case 3 :
                if (empty($params['user_ids'])) {
                    $this->goReference($this->getControllerName('index'), [
                        'danger' => '指定用户群体时需要手动填写用户ID',
                        'list' => $params
                    ]);
                }
                $user = $this->listUser(null, 'openid', $params['user_ids']);
                break;

            default :
                $user = null;
                break;
        }

        foreach ((array) $user as $to) {
            $params['to'] = $to;
            try {
                Yii::$app->wx->sendTplMsg($params, $field);
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->goReference($this->getControllerName('index'), [
            'info' => '模板消息发送任务已经执行'
        ]);
    }
}
