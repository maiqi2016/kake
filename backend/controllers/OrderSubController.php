<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;
use yii\helpers\Url;

/**
 * 子订单管理
 *
 * @auth-inherit-except add front sort
 */
class OrderSubController extends GeneralController
{
    // 模型
    public static $modelName = 'OrderSub';

    // 模型描述
    public static $modelInfo = '子订单';

    /**
     * @var string 模态框的名称
     */
    public static $ajaxModalListTitle = '选择子订单';

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['price'];

    public static $stateOk = [
        5, // 已入住
        6, // 已完成
    ];

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '主订单',
                'value' => 'order/index',
                'level' => 'info',
                'icon' => 'link',
                'params' => ['order_number']
            ],
            [
                'text' => '日志',
                'value' => 'order-instructions-log/index',
                'params' => function ($record) {
                    return ['order_sub_id' => $record['id']];
                },
                'level' => 'default',
                'icon' => 'paperclip'
            ],
            [
                'br' => true,
                'text' => '同意预约',
                'value' => 'agree-order',
                'level' => 'success confirm-button',
                'icon' => 'thumbs-up',
                'show_condition' => function ($record) {
                    return $record['state'] == 1;
                }
            ],
            [
                'text' => '拒绝预约',
                'type' => 'script',
                'level' => 'warning',
                'icon' => 'thumbs-down',
                'value' => '$.showPage',
                'params' => function ($record) {
                    return [
                        'order-instructions-log.refuse',
                        [
                            'order_sub_id' => $record['id'],
                            'type' => 2
                        ],
                    ];
                },
                'show_condition' => function ($record) {
                    return $record['state'] == 1;
                }
            ],
            [
                'br' => true,
                'text' => '同意退款',
                'value' => 'agree-refund',
                'level' => 'success confirm-button',
                'icon' => 'thumbs-up',
                'show_condition' => function ($record) {
                    return $record['state'] == 3;
                }
            ],
            [
                'text' => '拒绝退款',
                'type' => 'script',
                'level' => 'warning',
                'icon' => 'thumbs-down',
                'value' => '$.showPage',
                'params' => function ($record) {
                    return [
                        'order-instructions-log.refuse',
                        [
                            'order_sub_id' => $record['id'],
                            'type' => 1
                        ],
                    ];
                },
                'show_condition' => function ($record) {
                    return $record['state'] == 3;
                }
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function editOperation()
    {
        return [
            [
                'text' => '违约退款',
                'type' => 'script',
                'value' => 'alert("刺不刺激，功能待完善")',
                'level' => 'warning confirm-button',
                'icon' => 'exclamation-sign',
                'show_condition' => function ($record) {
                    return $record['state'] == 0;
                }
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListOperations()
    {
        return [
            [
                'text' => '选定',
                'script' => true,
                'value' => '$.modalRadioValueToInput("radio", "order_sub_id")',
                'icon' => 'flag'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexFilter()
    {
        return [
            'order_number' => [
                'table' => 'order',
                'field' => 'order_number',
                'elem' => 'input',
                'equal' => true
            ],
            'username' => [
                'table' => 'user',
                'elem' => 'input'
            ],
            'check_in_name' => 'input',
            'check_in_phone' => 'input',
            'check_in_time' => [
                'elem' => 'input',
                'type' => 'date',
                'between' => true
            ],
            'conformation_number' => [
                'elem' => 'input',
                'equal' => true
            ],
            'payment_state' => [
                'table' => 'order',
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListFilter()
    {
        return [
            'order_number' => [
                'table' => 'order',
                'field' => 'order_number',
                'elem' => 'input',
                'equal' => true
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexSorter()
    {
        return [
            'id',
            'order_number',
            'price',
            'payment_state' => [
                'table' => 'order'
            ],
            'payment_method' => [
                'table' => 'order'
            ],
            'add_time',
            'update_time',
            'state'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'order_number' => [
                'table' => 'order',
                'field' => 'order_number',
                'code',
                'color' => 'default',
                'tip'
            ],
            'name' => [
                'title' => '套餐',
                'max-width' => '150px'
            ],
            'conformation_number' => [
                'empty',
                'tip'
            ],
            'check_in_name' => 'empty',
            'check_in_phone' => 'empty',
            'check_in_time' => 'empty',
            'price' => 'code',
            'payment_state' => [
                'table' => 'order',
                'code',
                'info',
                'color' => [
                    0 => 'warning',
                    1 => 'success',
                    2 => 'default'
                ]
            ],
            'add_time' => 'tip',
            'update_time' => 'tip',
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'info',
                    1 => 'primary',
                    2 => 'primary',
                    3 => 'warning',
                    4 => 'default',
                    5 => 'success',
                    6 => 'default'
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListAssist()
    {
        return [
            'id' => 'code',
            'order_number' => [
                'table' => 'order',
                'field' => 'order_number',
                'code'
            ],
            'price' => 'code',
            'name' => [
                'title' => '套餐'
            ],
            'check_in_name' => 'empty',
            'check_in_phone' => 'empty',
            'state' => [
                'code',
                'info'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        return [
            'check_in_name',
            'check_in_phone',
            'check_in_time' => [
                'type' => 'date'
            ],
            'conformation_number',
            'state' => [
                'elem' => 'select'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function ajaxModalListCondition()
    {
        return self::indexCondition();
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        return [
            'join' => [
                ['table' => 'order'],
                ['table' => 'product_package'],
                [
                    'left_table' => 'order',
                    'table' => 'user'
                ]
            ],
            'select' => [
                'product_package.name',
                'order.order_number',
                'order.payment_state',
                'order_sub.*'
            ],
            'order' => [
                'order_sub.update_time DESC'
            ],
        ];
    }

    /**
     * 选择订单 - 弹出层
     *
     * @auth-pass-all
     */
    public function actionAjaxModalList()
    {
        return $this->showList();
    }

    /**
     * 同意预约
     *
     * @access public
     *
     * @param integer $id
     */
    public function actionAgreeOrder($id)
    {
        $result = $this->service('order.agree-order', [
            'order_sub_id' => $id,
            'user_id' => $this->user->id
        ]);

        if (is_string($result)) {
            Yii::$app->session->setFlash('danger', Yii::t('common', $result));
        } else {
            Yii::$app->wx->notice->send([
                'touser' => $result['openid'],
                'template_id' => 'f4MEfyNJQafoJl70GYAlblo_L0dBhf-E1cwUjGme16U',
                'url' => null,
                'data' => [
                    'first' => "您的预约入住已被通过\n",
                    'keyword1' => [
                        $result['name'],
                        '#999'
                    ],
                    'keyword2' => [
                        '在线预订',
                        '#999'
                    ],
                    'keyword3' => [
                        $result['check_in_name'],
                        '#999'
                    ],
                    'keyword4' => [
                        $result['check_in_phone'],
                        '#999'
                    ],
                    'keyword5' => [
                        $result['check_in_time'],
                        '#999'
                    ],
                    'remark' => [
                        "\n如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                        '#fda443'
                    ]
                ]
            ]);
            Yii::$app->session->setFlash('success', '同意预约操作完成');
        }

        $this->goReference($this->getControllerName('index'));
    }

    /**
     * 拒绝预约
     *
     * @access public
     */
    public function actionRefuseOrder()
    {
        $params = Yii::$app->request->post();
        $result = $this->service('order.refuse-order', [
            'order_sub_id' => $params['order_sub_id'],
            'remark' => $params['remark'],
            'user_id' => $this->user->id
        ]);

        if (is_string($result)) {
            Yii::$app->session->setFlash('danger', Yii::t('common', $result));
        } else {
            Yii::$app->wx->notice->send([
                'touser' => $result['openid'],
                'template_id' => 'f4MEfyNJQafoJl70GYAlblo_L0dBhf-E1cwUjGme16U',
                'url' => null,
                'data' => [
                    'first' => "您的预约入住已被拒绝\n",
                    'keyword1' => [
                        $result['name'],
                        '#999'
                    ],
                    'keyword2' => [
                        '在线预订',
                        '#999'
                    ],
                    'keyword3' => [
                        $result['check_in_name'],
                        '#999'
                    ],
                    'keyword4' => [
                        $result['check_in_phone'],
                        '#999'
                    ],
                    'keyword5' => [
                        $result['check_in_time'],
                        '#999'
                    ],
                    'remark' => [
                        "\n" . $params['remark'] . "\n\n如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                        '#fda443'
                    ]
                ]
            ]);
            Yii::$app->session->setFlash('success', '拒绝预约操作完成');
        }

        $this->goReference($this->getControllerName('index'));
    }

    /**
     * 同意退款
     *
     * @access public
     *
     * @param integer $id
     */
    public function actionAgreeRefund($id)
    {
        $order = $this->service('order.agree-refund', [
            'order_sub_id' => $id,
            'user_id' => $this->user->id
        ]);

        if (is_string($order)) {
            Yii::$app->session->setFlash('danger', Yii::t('common', $order));
            $this->goReference($this->getControllerName('index'));
        }

        $orderNo = $order['order_number'];
        $refundNo = $order['id'] . 'R' . $orderNo;

        // 支付宝
        $success = true;
        $payment = OrderController::$payment[$order['payment_method']];
        if ($payment == 'AliPay') {
            $price = intval($order['price']) / 100;
            $result = Yii::$app->ali->alipayTradeRefund($orderNo, $refundNo, $price);
            if (is_string($result)) {
                $success = false;
                $info = $result;
            } else {
                $info = '退款申请已经提交';
            }
        } else {
            try {
                $result = Yii::$app->wx->payment->refund($orderNo, $refundNo, $order['total_price'], $order['price']);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            if (isset($result) && isset($result->err_code_des)) {
                $success = false;
                $info = $result->err_code_des;
            } else {
                $info = '退款申请已经提交';
            }
        }

        $info = '<' . $payment . ' : 接口反馈> ' . $info;
        Yii::info('UID:' . $this->user->id . ' -> ' . $info);

        if ($success) {
            Yii::$app->wx->notice->send([
                'touser' => $order['openid'],
                'template_id' => 'X3ZhVd77-4eddoTx2PJzkWAk7Cu0vSqGNXX5sUYbHcg',
                'url' => null,
                'data' => [
                    'first' => "您的退款申请已被通过\n",
                    'keyword1' => [
                        '　' . $order['order_number'],
                        '#999'
                    ],
                    'keyword2' => [
                        date('Y-m-d H:i:s'),
                        '#999'
                    ],
                    'keyword3' => [
                        Helper::money($order['price'] / 100, '%s'),
                        '#999'
                    ],
                    'remark' => [
                        "\n如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                        '#fda443'
                    ]
                ]
            ]);
            Yii::$app->session->setFlash('success', $info);
        } else {
            Yii::$app->session->setFlash('danger', $info);
        }

        $this->goReference($this->getControllerName('index'));
    }

    /**
     * 拒绝退款
     *
     * @access public
     */
    public function actionRefuseRefund()
    {
        $params = Yii::$app->request->post();
        $result = $this->service('order.refuse-refund', [
            'order_sub_id' => $params['order_sub_id'],
            'remark' => $params['remark'],
            'user_id' => $this->user->id
        ]);

        if (is_string($result)) {
            Yii::$app->session->setFlash('danger', Yii::t('common', $result));
        } else {
            Yii::$app->wx->notice->send([
                'touser' => $result['openid'],
                'template_id' => 'X3ZhVd77-4eddoTx2PJzkWAk7Cu0vSqGNXX5sUYbHcg',
                'url' => null,
                'data' => [
                    'first' => "您的退款申请已被拒绝\n",
                    'keyword1' => [
                        '　' . $result['order_number'],
                        '#999'
                    ],
                    'keyword2' => [
                        date('Y-m-d H:i:s'),
                        '#999'
                    ],
                    'keyword3' => [
                        Helper::money($result['price'] / 100, '%s'),
                        '#999'
                    ],
                    'remark' => [
                        "\n" . $params['remark'] . "\n\n如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                        '#fda443'
                    ]
                ]
            ]);
            Yii::$app->session->setFlash('success', '拒绝退款操作完成');
        }

        $this->goReference($this->getControllerName('index'));
    }
}
