<?php

namespace backend\controllers;

/**
 * 产品供应商管理
 *
 * @auth-inherit-except front sort
 */
class ProductSupplierController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductSupplier';

    // 模型描述
    public static $modelInfo = '产品供应商';

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增产品供应商',
                'value' => 'product-supplier/add',
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
            'id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'name' => 'input',
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
            'name'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'name' => [
                'max-width' => '250px'
            ],
            'contact' => ['empty'],
            'address' => [
                'empty',
                'max-width' => '400px'
            ],
            'state' => [
                'code',
                'color' => 'auto',
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
            'name' => [
                'placeholder' => '32个字以内'
            ],
            'contact' => [
                'placeholder' => '非必填'
            ],
            'address' => [
                'label' => 5,
                'placeholder' => '非必填，64个字以内'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }
}
