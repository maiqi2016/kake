<?php

namespace backend\controllers;

/**
 * 订单发票管理
 *
 * @auth-inherit-except front sort
 */
class OrderBillController extends GeneralController
{
    // 模型
    public static $modelName = 'OrderBill';

    // 模型描述
    public static $modelInfo = '订单发票';

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['price'];

    /**
     * @var array Hook
     */
    public static $hookLogic = ['handle'];

    /**
     * @var array Field
     */
    public static $_handle = [
        0 => '待处理',
        1 => '已处理'
    ];

    /**
     * 是否处理
     *
     * @param array $record
     *
     * @return boolean
     */
    public static function handleLogic($record)
    {
        return !empty($record['courier_number']);
    }

    /**
     * 是否处理反向逻辑
     *
     * @param integer $index
     *
     * @return array
     */
    public static function handleReverseWhereLogic($index)
    {
        $indexes = [
            0 => [
                [
                    'or',
                    ['order_bill.courier_number' => ''],
                    ['order_bill.courier_number' => null],
                ]
            ],
            1 => [
                [
                    '<>',
                    'order_bill.courier_number',
                    ''
                ],
                [
                    'NOT',
                    ['order_bill.courier_number' => null],
                ]
            ]
        ];

        return isset($indexes[$index]) ? $indexes[$index] : [];
    }

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增订单发票',
                'value' => 'order-bill/add',
                'icon' => 'plus'
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
                'elem' => 'input',
                'equal' => true
            ],
            'courier_number' => 'input',
            'courier_company' => 'input',
            'invoice_title' => 'input',
            'tax_number' => [
                'elem' => 'input',
                'equal' => true
            ],
            'address' => 'input',
            'handle' => [
                'title' => '状况',
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
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'order_number' => [
                'code',
                'table' => 'order'
            ],
            'courier_number' => [
                'code',
                'empty',
                'tip'
            ],
            'courier_company' => [
                'empty',
                'tip'
            ],
            'price' => [
                'code',
                'title' => '票据金额'
            ],
            'invoice_title',
            'tax_number' => 'code',
            'address',
            'handle' => [
                'title' => '状况',
                'info',
                'code',
                'color' => [
                    0 => 'warning',
                    1 => 'success'
                ]
            ],
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
            'order_sub_id' => [
                'readonly' => true,
                'same_row' => true
            ],
            'select_order' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择订单',
                'script' => '$.showPage("order-sub.list", {state: "5,6"})'
            ],
            'courier_number',
            'courier_company',
            'invoice_title' => [
                'placeholder' => '个人或公司名全称'
            ],
            'tax_number' => [
                'placeholder' => '可不填'
            ],
            'address' => [
                'label' => 5,
                'placeholder' => '发票寄送地址'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
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
                ['table' => 'order_sub'],
                [
                    'left_table' => 'order_sub',
                    'table' => 'order'
                ]
            ],
            'select' => [
                'order.order_number',
                'order_sub.price',
                'order_bill.*'
            ],
        ]);
    }
}
