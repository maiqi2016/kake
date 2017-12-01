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

    // 列表子订单弹窗标题
    public static $ajaxModalListTitle = '选择子订单';

    // 当前用户ID
    public static $uid;

    // 核销状态
    public static $_sold_state;

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['price'];

    /**
     * @var array 已完成的子订单状态集
     */
    public static $stateOk = [
        5,
        // 已入住
        6,
        // 已完成
    ];

    /**
     * @inheritdoc
     */
    public static function soldOperationForm()
    {
        return [
            [
                'text' => '导出Excel',
                'type' => 'attr',
                'level' => 'success condition-global-event',
                'params' => [
                    'event' => 'export'
                ],
                'icon' => 'save'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function mySoldOperationForm()
    {
        return self::soldOperationForm();
    }

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
     * @inheritdoc
     */
    public static function soldOperation()
    {
        return [
            [
                'text' => '编辑',
                'value' => 'order-sold-code/edit',
                'icon' => 'pencil',
                'params' => function ($record) {
                    return ['id' => $record['sold_id']];
                }
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function mySoldOperation()
    {
        $operation = self::soldOperation();
        $operation[0]['value'] = 'order-sold-code/my-edit';

        return $operation;
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
                'value' => '$.alert("功能待完善")',
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
                'text' => '提交选择',
                'type' => 'script',
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
    public static function soldFilter()
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
            'product_supplier_name' => [
                'title' => '供应商',
                'table' => 'product_supplier',
                'field' => 'id',
                'list_table' => 'product_supplier',
                'list_value' => 'name',
                'value' => parent::SELECT_KEY_ALL
            ],
            'code' => [
                'title' => '核销码',
                'elem' => 'input',
                'table' => 'order_sold_code'
            ],
            'sold_state' => [
                'title' => '核销状态',
                'table' => 'order_sold_code',
                'field' => 'state',
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function mySoldFilter()
    {
        return self::soldFilter();
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
    public static function soldSorter()
    {
        $sorter = self::indexSorter();
        unset($sorter['payment_state']);

        return $sorter;
    }

    /**
     * @inheritDoc
     */
    public static function mySoldSorter()
    {
        return self::soldSorter();
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
            'username' => [
                'tip',
                'table' => 'user'
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
            'product_supplier_name' => [
                'tip',
                'title' => '供应商',
                'code'
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
    public static function soldAssist()
    {
        return [
            'id' => 'code',
            'order_number' => [
                'table' => 'order',
                'field' => 'order_number',
                'code',
                'color' => 'default'
            ],
            'username' => [
                'table' => 'user'
            ],
            'name' => [
                'title' => '套餐',
                'max-width' => '350px'
            ],
            'price' => 'code',
            'product_supplier_name' => [
                'title' => '供应商',
                'code',
                'color' => 'primary'
            ],
            'add_time' => 'tip',
            'update_time' => 'tip',
            'code' => [
                'title' => '核销码',
                'max-width' => '150px',
                'tpl' => '<p class="bg-info text-center">%s</p>',
                'color' => 'default'
            ],
            'producer_username' => [
                'title' => '分销商'
            ],
            'sold_state' => [
                'title' => '核销状态',
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'success',
                    2 => 'warning'
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function soldExportAssist()
    {
        return [
            'id',
            'order_number' => '订单编号',
            'username' => '购买用户',
            'name' => '套餐',
            'price',
            'product_supplier_name' => '供应商',
            'code' => '核销码',
            'producer_username' => '分销商',
            'sold_state_info' => '核销状态',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function mySoldExportAssist()
    {
        return self::soldExportAssist();
    }

    /**
     * @inheritDoc
     */
    public static function mySoldAssist()
    {
        return self::soldAssist();
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
                ],
                [
                    'left_table' => 'product_package',
                    'table' => 'product_supplier'
                ]
            ],
            'select' => [
                'product_package.name',
                'order.order_number',
                'order.payment_state',
                'order_sub.*',
                'user.username',
                'product_supplier.name AS product_supplier_name'
            ],
            'order' => [
                'order_sub.update_time DESC'
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function soldCondition()
    {
        $condition = self::indexCondition();
        $condition['where'][] = [
            '>',
            'product_package.product_supplier_id',
            0
        ];
        $condition['where'][] = [
            'not',
            ['order_sold_code.code' => null]
        ];

        $condition['join'] = array_merge($condition['join'], [
            [
                'table' => 'order_sold_code',
                'left_on_field' => 'id',
                'right_on_field' => 'order_sub_id'
            ],
            [
                'left_table' => 'order',
                'table' => 'producer_log'
            ],
            [
                'left_table' => 'producer_log',
                'table' => 'user',
                'as' => 'producer_user',
                'left_on_field' => 'producer_id'
            ]
        ]);
        $condition['select'] = array_merge($condition['select'], [
            'order_sold_code.code',
            'order_sold_code.id AS sold_id',
            'order_sold_code.state AS sold_state',
            'producer_user.username AS producer_username',
        ]);

        return $condition;
    }

    /**
     * @inheritDoc
     */
    public function mySoldCondition()
    {
        $condition = self::soldCondition();
        $condition['where'][] = [
            'product_package.product_supplier_id' => $this->user->supplier
        ];

        return $condition;
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        $model = parent::model(self::$modelName);
        self::$_sold_state = $model->_sold_state;
        $record = $this->getFieldInfo($record, 'sold_state');

        if (!empty($record['code'])) {
            $record['code'] = wordwrap($record['code'], 4, ' ', true);
        }

        return parent::sufHandleField($record, $action, $callback);
    }

    /**
     * 选择订单 - 编辑发票时弹出层
     *
     * @auth-pass-role 1
     */
    public function actionAjaxModalList()
    {
        return $this->showList();
    }

    /**
     * 供应商订单列表
     */
    public function actionSold()
    {
        return $this->showList();
    }

    /**
     * 供应商订单导出
     */
    public function actionSoldExport()
    {
        $this->exportList('供应商订单.' . date('Ymd'));
    }

    /**
     * 我的核销
     *
     * @auth-pass-role 1,9
     */
    public function actionMySold()
    {
        $this->user->supplier = $this->listSupplier($this->user->id);

        return $this->showList('mySold');
    }

    /**
     * 我的供应商订单导出
     *
     * @auth-pass-role 1,9
     */
    public function actionMySoldExport()
    {
        $this->user->supplier = $this->listSupplier($this->user->id);
        $this->exportList('我的供应商订单.' . date('Ymd'));
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
            $flash['danger'] = Yii::t('common', $result);
        } else {
            if (!empty($result['openid'])) {
                Yii::$app->wx->sendTplMsg([
                    'to' => $result['openid'],
                    'tpl' => 'f4MEfyNJQafoJl70GYAlblo_L0dBhf-E1cwUjGme16U',
                    'header' => '您的预约入住已被通过',
                    'footer' => "如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                ], [
                    $result['name'],
                    '在线预订',
                    $result['check_in_name'],
                    $result['check_in_phone'],
                    $result['check_in_time'],
                ]);
            }

            $flash['success'] = '同意预约操作完成';
        }

        $this->goReference($this->getControllerName('index'), $flash);
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
            $flash['danger'] = Yii::t('common', $result);
        } else {
            if (!empty($result['openid'])) {
                Yii::$app->wx->sendTplMsg([
                    'to' => $result['openid'],
                    'tpl' => 'f4MEfyNJQafoJl70GYAlblo_L0dBhf-E1cwUjGme16U',
                    'header' => '您的预约入住已被拒绝',
                    'footer' => $params['remark'] . PHP_EOL . PHP_EOL . "如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                ], [
                    $result['name'],
                    '在线预订',
                    $result['check_in_name'],
                    $result['check_in_phone'],
                    $result['check_in_time'],
                ]);
            }

            $flash['success'] = '拒绝预约操作完成';
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
        $order = $this->service(parent::$apiDetail, [
            'table' => 'order_sub',
            'join' => [
                ['table' => 'order']
            ],
            'select' => [
                'order.id',
                'order.order_number',
                'order.payment_method',
                'order.price AS total_price',
                'order_sub.price'
            ],
            'where' => [['order_sub.id' => $id]]
        ]);

        if (empty($order)) {
            $this->goReference($this->getControllerName('index'), [
                'danger' => Yii::t('common', 'abnormal operation')
            ]);
        }

        $orderNo = $order['order_number'];
        $refundNo = $id . 'R' . $orderNo;

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
                $this->goReference($this->getControllerName('index'), [
                    'danger' => $e->getMessage()
                ]);
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
            if (!empty($order['openid'])) {
                Yii::$app->wx->sendTplMsg([
                    'to' => $order['openid'],
                    'tpl' => 'X3ZhVd77-4eddoTx2PJzkWAk7Cu0vSqGNXX5sUYbHcg',
                    'header' => '您的退款申请已被通过',
                    'footer' => "如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                ], [
                    $order['order_number'],
                    date('Y-m-d H:i:s'),
                    Helper::money($order['price'] / 100, '%s'),
                ]);
            }

            $result = $this->service('order.agree-refund', [
                'order_sub_id' => $id,
                'user_id' => $this->user->id
            ]);

            if (is_string($result)) {
                $this->goReference($this->getControllerName('index'), [
                    'danger' => Yii::t('common', $result)
                ]);
            }

            $flash['success'] = $info;
        } else {
            $flash['danger'] = $info;
        }

        $this->goReference($this->getControllerName('index'), $flash);
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
            $flash['danger'] = Yii::t('common', $result);
        } else {
            if (!empty($result['openid'])) {
                Yii::$app->wx->sendTplMsg([
                    'to' => $result['openid'],
                    'tpl' => 'X3ZhVd77-4eddoTx2PJzkWAk7Cu0vSqGNXX5sUYbHcg',
                    'header' => '您的退款申请已被拒绝',
                    'footer' => $params['remark'] . PHP_EOL . PHP_EOL . "如有疑问请联系客服 " . Yii::$app->params['company_tel'],
                ], [
                    $result['order_number'],
                    date('Y-m-d H:i:s'),
                    Helper::money($result['price'] / 100, '%s'),
                ]);
            }

            $flash['success'] = '拒绝退款操作完成';
        }

        $this->goReference($this->getControllerName('index'));
    }
}
