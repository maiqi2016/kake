<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 余额账目管理
 *
 * @auth-inherit-except add edit front sort
 */
class ProducerQuotaController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerQuota';

    // 模型描述
    public static $modelInfo = '余额账目';

    // 当前用户ID
    public static $uid;

    // 佣金余额
    public static $quota;

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['quota'];

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
    public function pageDocument()
    {
        return [
            'my' => [
                'title_icon' => 'usd',
                'title_info' => '申请提现',
                'button_info' => '提交申请',
                'action' => 'withdraw',
                'info_perfect' => true
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'producer_name' => [
                'title' => '分销商',
                'elem' => 'input',
                'table' => 'user',
                'field' => 'username'
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'producer_name' => [
                'title' => '分销商',
                'code'
            ],
            'quota' => [
                'title' => '当前佣金余额',
                'price',
                'code'
            ],
            'add_time',
            'update_time',
            'state' => [
                'code',
                'info'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function myAssist()
    {
        return [
            'quota_surplus' => [
                'title' => '佣金余额',
                'elem' => 'text',
                'tag' => 'div',
                'class' => 'page-header',
                'label' => 6,
                'html' => true,
                'value' => '<h1>' . self::$quota . ' <small>提现金不能大于此余额</small></h1>'
            ],
            'quota' => [
                'title' => '提现金额',
                'class' => 'input-group-lg'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition('A'), [
            'from' => ['A' => 'producer_quota'],
            'join' => [
                [
                    'table' => 'user',
                    'left_on_field' => 'producer_id'
                ]
            ],
            'select' => [
                'user.username AS producer_name',
                'A.*'
            ],
            'as' => 'A',
            'where' => [
                [
                    'sub' => [
                        'select' => ['MAX(producer_quota.add_time)'],
                        'where' => ['`producer_id` = `A`.`producer_id`']
                    ],
                    'tpl' => "['A.add_time' => {SUB_QUERY}]"
                ],
                ['A.state' => 1]
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function myCondition()
    {
        return [
            'order' => ['add_time DESC']
        ];
    }

    /**
     * 我的佣金余额
     *
     * @auth-pass-role 1,10
     */
    public function actionMy()
    {
        $record = $this->showFormWithRecord([
            'producer_id' => self::$uid,
            'state' => 1
        ], null, true);

        self::$quota = Helper::money(empty($record) ? 0 : $record['quota']);

        return $this->showForm();
    }

    /**
     * 提现
     *
     * @auth-pass-role 1,10
     */
    public function actionWithdraw()
    {
        $reference = $this->getControllerName('my');
        $quota = Yii::$app->request->post('quota');

        $result = $this->applyWithdraw(self::$uid, $quota);
        if (is_string($result)) {
            $this->goReference($reference, [
                'danger' => $result
            ]);
        }

        $controller = $this->controller('producer-withdraw');
        $controller->actionAddForm($reference, 'add', [
            'producer_id' => self::$uid,
            'withdraw' => $quota
        ]);
    }

    /**
     * 申请提现
     *
     * @param integer $userId
     * @param float   $quota
     *
     * @return mixed
     */
    public function applyWithdraw($userId, $quota)
    {
        if (empty($quota) || $quota <= 0) {
            return '请输入申请提现金额';
        }

        if ($quota < Yii::$app->params['withdraw_min']) {
            return '提现金额不能小于 ' . Yii::$app->params['withdraw_min'];
        }

        // 判断是否有申请中的提现记录
        $controller = $this->controller('producer-withdraw');
        $withdraw = $controller->showFormWithRecord([
            'producer_id' => $userId,
            'state' => 1
        ], null, true, false);

        if (!empty($withdraw)) {
            return '上一次的提现申请还未被处理，请耐心等待';
        }

        // 判断提现是否超额
        $record = $this->showFormWithRecord([
            'producer_id' => $userId,
            'state' => 1
        ], 'my', true, false);

        $have = empty($record) ? 0 : $record['quota'];
        if ($quota > $have) {
            $have = Helper::money($have);

            return "佣金余额不足，可提现余额为：${have}";
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        parent::beforeAction($action);
        self::$uid = $this->user->id;

        return true;
    }
}
