<?php

namespace backend\controllers;

/**
 * 核销用户管理
 *
 * @auth-inherit-except front sort
 */
class ProductSupplierUserController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductSupplierUser';

    // 模型描述
    public static $modelInfo = '核销用户';

    /**
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增核销用户',
                'value' => 'product-supplier-user/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'product_supplier_id' => [
                'list_table' => 'product_supplier',
                'list_value' => 'name',
                'value' => parent::SELECT_KEY_ALL
            ],
            'username' => [
                'table' => 'user',
                'elem' => 'input',
                'equal' => true
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
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'username' => [
                'table' => 'user'
            ],
            'product_supplier_id' => [
                'list_table' => 'product_supplier',
                'list_value' => 'name',
                'info',
                'code'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        return [
            'product_supplier_id' => [
                'elem' => 'select',
                'list_table' => 'product_supplier',
                'list_value' => 'name',
                'title' => '供应商'
            ],
            'user_id' => [
                'readonly' => true,
                'same_row' => true,
                'title' => '用户'
            ],
            'select_user' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择用户',
                'script' => '$.showPage("user.list", {role: 0, state: 1, field_name: "user_id"})'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'user']
            ],
            'select' => [
                'product_supplier_user.*',
                'user.username'
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function editCondition()
    {
        return self::indexCondition();
    }
}
