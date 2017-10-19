<?php

namespace backend\controllers;

/**
 * 产品上游管理
 *
 * @auth-inherit-except front sort
 */
class ProductUpstreamController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductUpstream';

    // 模型描述
    public static $modelInfo = '产品上游';

    /**
     * @var string 模态框的名称
     */
    public static $ajaxModalListTitle = '选择上游';

    /**
     * @inheritDoc
     */
    public static function ajaxModalListOperations()
    {
        return [
            [
                'text' => '选定',
                'script' => true,
                'value' => '$.modalRadioValueToInput("radio", "product_upstream_id")',
                'icon' => 'flag'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增产品上游',
                'value' => 'product-upstream/add',
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
            'classify' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'name' => 'input',
            'product_region_id' => [
                'list_table' => 'product_region',
                'list_value' => 'name',
                'value' => parent::SELECT_KEY_ALL
            ],
            'principal' => 'input',
            'address' => 'input',
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
            'classify' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'name' => 'input',
            'product_region_id' => [
                'list_table' => 'product_region',
                'list_value' => 'name',
                'value' => parent::SELECT_KEY_ALL,
            ],
            'principal' => 'input',
            'address' => 'input',
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
            'name',
            'classify'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'classify' => [
                'code',
                'info',
                'color' => [
                    0 => 'primary',
                    1 => 'success',
                    2 => 'info',
                    3 => 'default'
                ]
            ],
            'name' => [
                'max-width' => '250px'
            ],
            'product_region_id' => [
                'list_table' => 'product_region',
                'list_value' => 'name',
                'info',
                'code'
            ],
            'principal' => [
                'empty',
                'tip'
            ],
            'contact' => [
                'empty',
                'tip'
            ],
            'address' => [
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
    public static function ajaxModalListAssist()
    {
        return [
            'classify' => [
                'code',
                'info',
                'color' => [
                    0 => 'primary',
                    1 => 'success',
                    2 => 'info',
                    3 => 'default'
                ]
            ],
            'name',
            'product_region_id' => [
                'list_table' => 'product_region',
                'list_value' => 'name',
                'info',
                'code'
            ],
            'address' => [
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
            'classify' => [
                'elem' => 'select',
                'value' => 1
            ],
            'name' => [
                'placeholder' => '64个字以内'
            ],
            'product_region_id' => [
                'list_table' => 'product_region',
                'list_value' => 'name',
                'elem' => 'select'
            ],
            'principal' => [
                'placeholder' => '非必填，32个字以内'
            ],
            'contact' => [
                'placeholder' => '非必填'
            ],
            'address' => [
                'label' => 5,
                'placeholder' => '64个字以内'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * 选择上游 - 弹出层
     *
     * @auth-pass-all
     */
    public function actionAjaxModalList()
    {
        return $this->showList();
    }
}
