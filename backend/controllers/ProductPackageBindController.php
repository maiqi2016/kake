<?php

namespace backend\controllers;

use Oil\src\Helper;
use Yii;

/**
 * 产品套餐打包管理
 *
 * @auth-inherit-except front sort
 */
class ProductPackageBindController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductPackageBind';

    // 模型描述
    public static $modelInfo = '套餐打包';

    /**
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增套餐打包',
                'value' => 'product-package-bind/add',
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
            'min_package_name' => [
                'title' => '左套餐名称',
                'elem' => 'input',
                'table' => 'min_table',
                'field' => 'name'
            ],
            'max_package_name' => [
                'title' => '右套餐名称',
                'elem' => 'input',
                'table' => 'max_table',
                'field' => 'name'
            ],
            'state' => [
                'value' => 1
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'product_id' => 'code',
            'min_package_name' => [
                'title' => '左套餐',
                'html' => true
            ],
            'max_package_name' => [
                'title' => '右套餐',
                'html' => true
            ],
            'base_price' => [
                'table' => 'product_package',
                'code',
                'tip'
            ],
            'add_time' => 'tip',
            'update_time' => 'tip',
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
            'left' => [
                'title' => '左套餐',
                'label' => 2,
                'readonly' => true,
                'same_row' => true
            ],
            'select_left' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择套餐',
                'script' => '$.showPage("product-package.list", {modal_size: "md", field_name: "left"})'
            ],

            'right' => [
                'title' => '右套餐',
                'label' => 2,
                'readonly' => true,
                'same_row' => true
            ],
            'select_right' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择套餐',
                'script' => '$.showPage("product-package.list", {modal_size: "md", field_name: "right"})'
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
    public function preHandleField($record, $action = null)
    {
        if (!empty($record['left']) && !empty($record['right'])) {

            if ($record['left'] == $record['right']) {
                $this->goReference($this->getControllerName($action), [
                    'danger' => '请选择相同产品的不同套餐',
                    'list' => $record
                ]);
            }

            list($minId, $record['min']) = explode(':', $record['left']);
            list($maxId, $record['max']) = explode(':', $record['right']);

            if ($minId != $maxId) {
                $this->goReference($this->getControllerName($action), [
                    'danger' => '请选择相同产品的套餐',
                    'list' => $record
                ]);
            }

            $record['product_id'] = $minId;
            $record['min'] = min($record['min'], $record['max']);
            $record['max'] = max($record['min'], $record['max']);

            unset($record['left'], $record['right']);
        }

        return parent::preHandleField($record, $action);
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (!empty($record['id'])) {
            $record['left'] = $record['product_id'] . ':' . $record['min'];
            $record['right'] = $record['product_id'] . ':' . $record['max'];

            $pad = function ($key) use ($record) {
                $str = str_pad($record[$key], 3, 0, STR_PAD_LEFT);

                return "<strong class='text-danger'>({$str}) </strong>";
            };

            $record['min_package_name'] = $pad('min') . $record['min_package_name'];
            $record['max_package_name'] = $pad('max') . $record['max_package_name'];
        }

        return parent::sufHandleField($record, $action, $callback);
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                [
                    'table' => 'product_package',
                    'as' => 'min_table',
                    'left_on_field' => 'min'
                ],
                [
                    'table' => 'product_package',
                    'as' => 'max_table',
                    'left_on_field' => 'max'
                ],
            ],
            'select' => [
                'min_table.name AS min_package_name',
                'max_table.name AS max_package_name',
                'product_package_bind.*'
            ],
        ]);
    }
}
