<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 分销提现管理
 *
 * @auth-inherit-except add edit front sort
 */
class ProducerWithdrawController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerWithdraw';

    // 模型描述
    public static $modelInfo = '分销提现';

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['withdraw'];

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return [
            [
                'text' => '通过申请',
                'value' => 'complete',
                'level' => 'success confirm-button',
                'icon' => 'eye-open',
                'show_condition' => function ($record) {
                    return $record['state'] == 1;
                }
            ],
            [
                'text' => '关闭申请',
                'value' => 'close',
                'level' => 'default confirm-button',
                'icon' => 'eye-close',
                'show_condition' => function ($record) {
                    return $record['state'] == 1;
                }
            ],
        ];
    }

    /**
     * @inheritDoc
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
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'producer_name' => [
                'title' => '分销商',
                'code',
                'color' => 'default'
            ],
            'withdraw' => [
                'title' => '提现金额',
                'price',
                'code'
            ],
            'account_type' => [
                'table' => 'producer_setting',
                'info',
                'code',
                'color' => [
                    0 => 'success',
                    1 => 'info'
                ]
            ],
            'account_number' => [
                'table' => 'producer_setting',
                'code',
                'color' => 'default'
            ],
            'add_time',
            'update_time' => 'tip',
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'warning',
                    2 => 'success'
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                [
                    'table' => 'user',
                    'left_on_field' => 'producer_id'
                ],
                [
                    'table' => 'producer_setting',
                    'left_on_field' => 'producer_id',
                    'right_on_field' => 'producer_id'
                ]
            ],
            'select' => [
                'user.username AS producer_name',
                'user.openid',
                'producer_setting.account_type',
                'producer_setting.account_number',
                'producer_withdraw.*'
            ]
        ]);
    }

    /**
     * 完成提现
     *
     * @access public
     *
     * @param integer $id
     */
    public function actionComplete($id)
    {
        $condition = self::indexCondition();
        $condition['table'] = 'producer_withdraw';
        $condition['where'] = [
            [
                'producer_withdraw.id' => $id,
                'producer_withdraw.state' => 1
            ]
        ];
        $record = $this->service(parent::$apiDetail, $condition);

        if (empty($record)) {
            Yii::$app->session->setFlash('danger', Yii::t('common', 'withdraw request not exists'));
            $this->goReference($this->getControllerName('index'));
        }

        if ($record['account_type'] == 0) {
            $result = Yii::$app->wx->merchant_pay->send([
                'partner_trade_no' => 'fx' . Helper::createOrderNumber('a', $record['producer_id']),
                'openid' => $record['openid'],
                'check_name' => 'NO_CHECK',
                'amount' => $record['withdraw'],
                'desc' => '支付分销提现',
                'spbill_create_ip' => Yii::$app->request->getUserIP()
            ]);

            if (!empty($result->err_code_des)) {
                Yii::$app->session->setFlash('danger', '<微信接口反馈> ' . $result->err_code_des);
                $this->goReference($this->getControllerName('index'));
            }
        }

        $result = $this->service('producer.withdraw', ['id' => $id]);
        if (is_string($result)) {
            Yii::$app->session->setFlash('danger', Yii::t('common', $result));
        } else {
            $quota = Helper::money($result['quota'] / 100);
            Yii::$app->session->setFlash('success', "提现申请处理完成，该分销商剩余佣金余额：{$quota}");
        }

        $this->goReference($this->getControllerName('index'));
    }

    /**
     * 关闭申请
     *
     * @access public
     *
     * @param integer $id
     */
    public function actionClose($id)
    {
        $reference = $this->getControllerName('index');
        $this->actionEditForm($reference, 'edit', [
            'id' => $id,
            'state' => 0
        ]);
    }
}
