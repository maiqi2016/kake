<?php

namespace backend\controllers;

use backend\components\ViewHelper;
use Oil\src\Helper;
use Yii;

/**
 * 分销记录管理
 *
 * @auth-inherit-except add edit front sort
 */
class ProducerLogController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerLog';

    // 模型描述
    public static $modelInfo = '分销记录';

    // 当前用户ID
    public static $uid;

    // 标记
    public static $success = '<span class="text-success">Yes</span>';
    public static $fail = '<span class="text-danger">No</span>';

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = [
        'log_amount_in',
        'log_amount_out'
    ];

    /**
     * @inheritdoc
     */
    public static function myOperations()
    {
        return [
            [
                'text' => '结算佣金',
                'value' => 'settlement',
                'level' => 'primary confirm-button',
                'icon' => 'usd'
            ],
            [
                'text' => '结算说明',
                'type' => 'script',
                'level' => 'warning',
                'value' => '$.showPage("producer-log.help")',
                'icon' => 'info-sign'
            ]
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
    public static function myOperation()
    {
        return self::indexOperation();
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
                'table' => 'producer_user',
                'field' => 'username'
            ],
            'buyer_name' => [
                'title' => '购买粉丝',
                'elem' => 'input',
                'table' => 'buyer_user',
                'field' => 'username'
            ],
            'product_id' => [
                'elem' => 'input',
                'equal' => true,
                'table' => 'producer_log'
            ],
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'title' => [
                'title' => '产品',
                'tip'
            ],
            'name' => [
                'title' => '上游',
                'tip'
            ],
            'buyer_name' => [
                'title' => '购买粉丝',
                'tip'
            ],
            'producer_name' => [
                'title' => '分销商'
            ],
            'product_id' => [
                'title' => '产品编号',
                'code',
                'tip'
            ],
            'type' => [
                'title' => '分佣类型',
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'primary'
                ],
                'tip'
            ],
            'survey_table' => [
                'html',
                'title' => '分佣额明细（取决于套餐状态）'
            ],
            'amount_in' => [
                'title' => '入围订单额',
                'price',
                'code'
            ],
            'amount_out' => [
                'title' => '淘汰订单额',
                'price',
                'code'
            ],
            'commission_table' => [
                'html',
                'title' => '分佣档次',
                'table' => 'product_producer'
            ],
            'counter_info' => [
                'title' => '可否结算',
                'html'
            ],
            'payment_state' => [
                'table' => 'order',
                'code',
                'info',
                'tip'
            ],
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'success',
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function myAssist()
    {
        $assist = self::indexAssist();
        unset($assist['producer_name'], $assist['type'], $assist['state']);
        $assist['counter'] = [
            'title' => '产品销量',
            'code'
        ];
        $assist['commission_quota'] = [
            'title' => '分佣金额',
            'code',
            'price' => 5,
        ];

        return $assist;
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                [
                    'table' => 'order',
                    'left_on_field' => 'id',
                    'right_on_field' => 'producer_log_id'
                ],
                [
                    'table' => 'producer_product',
                    'left_on_field' => [
                        'product_id',
                        'producer_id'
                    ],
                    'right_on_field' => [
                        'product_id',
                        'producer_id'
                    ]
                ],
                ['table' => 'product'],
                [
                    'table' => 'user',
                    'as' => 'buyer_user'
                ],
                [
                    'table' => 'user',
                    'left_on_field' => 'producer_id',
                    'as' => 'producer_user'
                ],
                [
                    'left_table' => 'product',
                    'table' => 'product_upstream'
                ],
            ],
            'select' => [
                'order.id AS order_id',
                'order.price',
                'order.payment_state',
                'producer_product.type',
                'buyer_user.username AS buyer_name',
                'producer_user.username AS producer_name',
                'product.title',
                'product_upstream.name',
                'producer_log.id',
                'producer_log.product_id',
                'producer_log.state'
            ],
            'where' => [
                // ['order.payment_state' => 1],
                [
                    '<',
                    'order.state',
                    2
                ],
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function myCondition()
    {
        $condition = $this->indexCondition();

        $condition['join'][] = [
            'left_table' => 'product',
            'table' => 'attachment',
            'left_on_field' => 'attachment_cover'
        ];

        $condition['select'] = array_merge($condition['select'], [
            'product.attachment_cover',
            'attachment.deep_path AS cover_deep_path',
            'attachment.filename AS cover_filename'
        ]);

        $condition['where'][1] = ['order.state' => 1];

        $condition['where'] = array_merge($condition['where'], [
            ['producer_log.producer_id' => self::$uid],
            ['producer_log.producer_id' => self::$uid],
            ['producer_log.state' => 1]
        ]);

        return $condition;
    }

    /**
     * 我的分销记录
     *
     * @auth-pass-role 1,10
     */
    public function actionMy()
    {
        return parent::showList();
    }

    /**
     * 结算帮助中心
     *
     * @auth-pass-role 1,10
     */
    public function actionAjaxModalHelp()
    {
        $this->modal('producer-log/help', [], '结算说明');
    }

    /**
     * 分销订单结算
     *
     * @auth-pass-role 1,10
     */
    public function actionSettlement()
    {
        $result = $this->settlement();

        $this->goReference($this->getControllerName('my'), [
            'info' => $result
        ]);
    }

    /**
     * 结算佣金
     *
     * @access public
     * @return string
     */
    public function settlement()
    {
        list($list) = $this->showList('my', true, false, [
            'size' => 0
        ]);

        foreach ($list as $key => $item) {
            if (empty($item['sub_counter'])) {
                unset($list[$key]);
            }
        }

        if (empty($list)) {
            return '暂无可结算的分销订单';
        }

        $quota = 0;
        $_list = [];
        foreach ($list as $item) {
            $log = Helper::pullSome($item, [
                'amount_in' => 'log_amount_in',
                'amount_out' => 'log_amount_out',
                'sub_counter' => 'log_sub_counter',
                'commission_quota' => 'log_commission_quota'
            ]);

            $log = $this->preHandleField($log);
            $_list[$item['id']] = $log;
            $quota += $item['commission_quota'];
        }

        $result = $this->service('producer.settlement', [
            'log' => $_list,
            'quota' => (int) ($quota * 100),
            'user_id' => self::$uid
        ]);

        if (is_string($result)) {
            return $result;
        } else {
            $number = count($list);
            $quota = Helper::money($quota);
            $after = Helper::money($result['afterQuota'] / 100);

            return "本次结算订单共计：${number}个，佣金共计：${quota} (保留到小数点后两位)，结算后总佣金余额：${after}";
        }
    }

    /**
     * 使用订单 ID 串列表子订单
     *
     * @access private
     *
     * @param array $orderIds
     *
     * @return array
     */
    private function listOrderSubByOrderIds($orderIds)
    {
        $list = $this->service(parent::$apiList, [
            'table' => 'order_sub',
            'select' => [
                'order_id',
                'product_package_id',
                'price',
                'state'
            ],
            'where' => [
                [
                    'in',
                    'order_id',
                    $orderIds
                ],
                [
                    '>=',
                    'price',
                    Yii::$app->params['commission_min_price'] * 100
                ]
            ],
        ]);

        $_list = $package = [];
        $orderSub = $this->controller('order-sub');
        foreach ($list as $item) {

            $item = $this->callMethod('sufHandleField', $item, [$item], $orderSub);
            $state = $item['state'];

            if (empty($_list[$item['order_id']])) {
                $_list[$item['order_id']] = [
                    'sub_counter' => 0,
                    'sub_counter_out' => 0,
                    'amount_in' => 0,
                    'amount_out' => 0
                ];
            }

            $_item = &$_list[$item['order_id']];
            if (in_array($state, OrderSubController::$stateOk)) {
                $_item['sub_counter'] += 1;
                $_item['amount_in'] += $item['price'];
            } else {
                $_item['sub_counter_out'] += 1;
                $_item['amount_out'] += $item['price'];
            }

            if (empty($_item[$state])) {
                $_item[$state] = [
                    'info' => $item['state_info'],
                    'number' => 1,
                    'amount' => $item['price'],
                    'pass' => in_array($state, OrderSubController::$stateOk) ? self::$success : self::$fail
                ];
            } else {
                $_item[$state]['number'] += 1;
                $_item[$state]['amount'] += $item['price'];
            }

            $package[$item['order_id']][] = $item;
        }

        return [
            $_list,
            $package
        ];
    }

    /**
     * 产品分佣达标统计
     *
     * @access private
     *
     * @param integer $userId
     * @param array   $productIds
     *
     * @return array
     */
    private function productCounter($userId, $productIds = null)
    {
        $condition = [
            'table' => 'producer_log',
            'join' => [
                [
                    'table' => 'order',
                    'left_on_field' => 'id',
                    'right_on_field' => 'producer_log_id'
                ]
            ],
            'select' => [
                'producer_log.*',
                'order.id AS order_id'
            ],
            'where' => [
                ['producer_log.producer_id' => $userId],
                ['producer_log.state' => 1]
            ]
        ];

        if ($productIds) {
            $condition['where'][] = [
                'in',
                'producer_log.product_id',
                $productIds
            ];
        }

        $list = $this->service(parent::$apiList, $condition);

        list($list, $orderIds) = Helper::valueToKey($list, 'order_id');
        list($subList) = $this->listOrderSubByOrderIds($orderIds);

        $counter = [];
        foreach ($list as $id => $item) {

            $product = $item['product_id'];
            if (!isset($counter[$product])) {
                $counter[$product] = 0;
            }

            if (!empty($subList[$id]['sub_counter'])) {
                $counter[$product] += 1;
            }
        }

        return [
            $counter,
            $subList
        ];
    }

    /**
     * 在前置字段处理前处理列表
     *
     * @param array $list
     *
     * @return array
     */
    public function sufHandleListBeforeField($list)
    {
        list($list, $orderIds) = Helper::valueToKey($list, 'order_id');
        list($subList, $package) = $this->listOrderSubByOrderIds($orderIds);

        foreach ($list as $key => &$item) {
            if (empty($subList[$item['order_id']])) {
                unset($list[$key]);
                continue;
            }
            $item['survey'] = $subList[$item['order_id']];
            $survey = Helper::popSome($item['survey'], [
                'sub_counter',
                'sub_counter_out',
                'amount_in',
                'amount_out'
            ]);
            $item = array_merge($item, $survey);

            $item['package'] = $package[$item['order_id']];
        }

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (in_array($action, [
            'index',
            'my'
        ])) {
            // 生成封面图附件地址
            $record = $this->createAttachmentUrl($record, ['attachment_cover' => 'cover']);

            $productCtrl = $this->controller('product');
            $data = $this->callMethod('sufHandleField', [], [
                ['id' => $record['product_id']],
                'listProduct'
            ], $productCtrl);

            $key = ProductController::$type[$record['type']];
            $record['commission_data'] = empty($data['commission_data_' . $key]) ? [] : $data['commission_data_' . $key];
            $record['commission_table'] = empty($data['commission_table_' . $key]) ? null : $data['commission_table_' . $key];

            $orderCtrl = $this->controller('order');
            $record = $this->callMethod('sufHandleField', [], [$record], $orderCtrl);

            $record['counter_info'] = $record['sub_counter'] ? self::$success : self::$fail;
            $record['survey_table'] = ViewHelper::createTable($record['survey'], [
                'info' => '状态',
                'number' => '个数',
                'amount' => '总金额',
                'pass' => '分佣',
            ], [
                'number' => '× %s',
                'amount' => '￥%s'
            ], [
                'info' => 30,
                'number' => 20,
                'amount' => 30,
                'pass' => 20,
            ]);
            unset($record['survey']);
        }

        return parent::sufHandleField($record, $action, $callback);
    }

    /**
     * 在前置字段处理后处理列表
     *
     * @param array  $list
     * @param string $action
     *
     * @return array
     */
    public function sufHandleListAfterField($list, $action = null)
    {
        if ($action != 'my') {
            return $list;
        }

        $productIds = array_column($list, 'product_id');
        list($counter, $subList) = $this->productCounter(self::$uid, $productIds);

        foreach ($list as &$value) {

            $product = $value['product_id'];
            $order = $value['order_id'];

            $value['counter'] = 0;
            $value['commission_quota'] = 0;
            $value['commission_quota_out'] = 0;

            $description = null;
            foreach ($subList[$order] as $k => $v) {
                if (is_numeric($k)) {
                    $description .= $v['info'] . '*' . $v['number'] . ',';
                }
            }
            $value['description'] = rtrim($description, ',');

            $count = $value['counter'] = $counter[$product];
            foreach ($value['commission_data'] as $key => $item) {

                $in = $value['amount_in'];
                $out = $value['amount_out'];
                $total = $in + $out;

                if (!$key) {
                    $value['commission_quota_out'] = self::calCommission($value['type'], $out, $total, $item['commission'], $value['sub_counter_out']);
                }

                if ($count >= $item['from_sales'] && (empty($item['to_sales']) || $count <= $item['to_sales'])) {
                    $value['commission_quota'] = self::calCommission($value['type'], $in, $total, $item['commission'], $value['sub_counter']);
                    break;
                }
            }
        }

        return $list;
    }

    /**
     * 计算分佣金
     *
     * @access public
     *
     * @param string  $type
     * @param float   $inQuota
     * @param float   $totalQuota
     * @param float   $commission
     * @param integer $number
     *
     * @return float
     */
    public static function calCommission($type, $inQuota, $totalQuota, $commission, $number = 1)
    {
        $cal = 0;
        if (empty($inQuota) || empty($totalQuota) || empty($commission)) {
            return $cal;
        }

        $type = ProductController::$type[$type];
        $rate = $inQuota / $totalQuota;

        if ($type == 'percent') {
            $cal = $inQuota * (($commission / 100) * $rate);
        } else if ($type == 'fixed') {
            $cal = $commission * $rate * $number;
        }

        return $cal;
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
