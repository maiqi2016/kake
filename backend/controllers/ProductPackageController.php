<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 产品套餐管理
 *
 * @auth-inherit-except front sort
 */
class ProductPackageController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductPackage';

    // 模型描述
    public static $modelInfo = '产品套餐';

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = [
        'base_price',
        'price'
    ];

    public static $_status;

    public static $supplierIdAssist = [
        'title' => '核销供应商',
        'list_table' => 'product_supplier',
        'list_value' => 'name',
        'list_except' => [
            0 => '无需核销'
        ]
    ];

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增产品套餐',
                'value' => 'product-package/add',
                'params' => [
                    'product_id' => Yii::$app->request->get('product_id')
                ],
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '所属产品',
                'value' => 'product/index',
                'level' => 'info',
                'icon' => 'link',
                'params' => function ($record) {
                    return ['id' => $record['product_id']];
                }
            ],
            [
                'alt' => '排序',
                'level' => 'default',
                'icon' => 'sort-by-attributes',
                'type' => 'script',
                'value' => '$.sortField',
                'params' => function ($record) {
                    return [
                        'product-package.sort',
                        $record['id'],
                        $record['sort']
                    ];
                },
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function pageDocument()
    {
        return array_merge(parent::pageDocument(), [
            'ajax-modal-package' => [
                'modal' => true,
                'title_info' => '添加套餐',
                'button_info' => '添加套餐',
                'action' => <<<EOF
$.handleModalForm($(this), $.package, {
    fn: $.createTag,
    params: {
        containerName: "package",
        fieldName: "package_ids",
        fieldNameNew: "new_package_ids"
    }
});
EOF
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function indexFilter()
    {
        return [
            'product_id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'name' => 'input',
            'title' => [
                'title' => '产品',
                'elem' => 'input',
                'table' => 'product'
            ],
            'product_upstream_name' => [
                'title' => '上游名称',
                'elem' => 'input',
                'table' => 'product_upstream',
                'field' => 'name'
            ],
            'info' => 'input',
            'product_supplier_id' => array_merge(self::$supplierIdAssist, [
                'list_except' => null,
                'value' => parent::SELECT_KEY_ALL
            ]),
            'bidding' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => 1
            ],
            'status' => [
                'title' => '产品状态',
                'table' => 'product',
                'field' => 'state',
                'value' => 1
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexSorter()
    {
        return [
            'product_id',
            'price',
            'sort'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'product_id' => 'code',
            'name' => [
                'max-width' => '120px'
            ],
            'base_price' => [
                'code',
                'tip'
            ],
            'title' => [
                'title' => '产品',
                'tip'
            ],
            'product_upstream_name' => [
                'title' => '上游名称',
                'tip'
            ],
            'price' => 'code',
            'sale_price' => [
                'title' => '折后价格',
                'code'
            ],
            'bidding' => [
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'success'
                ],
                'info'
            ],
            'sort' => 'code',
            'purchase_limit' => [
                'code',
                'empty',
                'not_set_info' => '<span class="not-set">+∞</span>',
                'tip'
            ],
            'info' => [
                'max-width' => '250px',
                'tpl' => '<pre>%s</pre>'
            ],
            'product_supplier_id' => array_merge(self::$supplierIdAssist, [
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'primary'
                ],
                'tip'
            ]),
            'supplier_contact' => [
                'tip'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ],
            'status' => [
                'title' => '产品状态',
                'code',
                'color' => 'auto',
                'tip',
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
            'product_id' => [
                'value' => intval(Yii::$app->request->get('product_id')) ?: null,
                'readonly' => true,
                'same_row' => true
            ],
            'select_product' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择产品',
                'script' => '$.showPage("product.list", {state: 1})'
            ],
            'name' => [
                'placeholder' => '32个字以内'
            ],
            'base_price' => [
                'placeholder' => '保留到小数点后两位'
            ],
            'price' => [
                'placeholder' => '保留到小数点后两位'
            ],
            'bidding' => [
                'elem' => 'select',
                'tip' => '是否参与最低价格显示',
                'value' => 1
            ],
            'sort' => [
                'placeholder' => '大于零的整数，越小越靠前'
            ],
            'purchase_limit' => [
                'placeholder' => '0表示不限制',
                'tip' => '以用户为单位进行限购',
                'value' => \Yii::$app->params['default_purchase_limit']
            ],
            'info' => [
                'elem' => 'textarea',
                'row' => 8,
                'placeholder' => '256个字以内'
            ],
            'product_supplier_id' => array_merge(self::$supplierIdAssist, [
                'elem' => 'select',
                'value' => 0
            ]),
            'supplier_contact' => [
                'show' => ['product_supplier_id' => 'value != 0']
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * 套餐弹出层
     */
    public static function ajaxModalPackageAssist()
    {
        return [
            'name' => [
                'label' => 5,
                'placeholder' => '32个字以内'
            ],
            'base_price' => [
                'label' => 5,
                'placeholder' => '保留到小数点后两位'
            ],
            'price' => [
                'label' => 5,
                'placeholder' => '保留到小数点后两位'
            ],
            'bidding' => [
                'elem' => 'select',
                'tip' => '是否参与最低价格显示',
                'value' => 1
            ],
            'sort' => [
                'placeholder' => '大于零的整数，越小越靠前'
            ],
            'purchase_limit' => [
                'placeholder' => '0表示不限制',
                'tip' => '以用户为单位进行限购',
                'value' => \Yii::$app->params['default_purchase_limit']
            ],
            'info' => [
                'elem' => 'textarea',
                'label' => 8,
                'row' => 8,
                'placeholder' => '256个字以内'
            ],
            'product_supplier_id' => array_merge(self::$supplierIdAssist, [
                'elem' => 'select',
                'value' => 0
            ]),
            'supplier_contact' => [
                'show' => ['product_supplier_id' => 'value != 0']
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function preHandleField($record, $action = null)
    {
        if (!empty($record['product_supplier_id']) && empty($record['supplier_contact'])) {
            Yii::$app->session->setFlash('warning', '供应商联系电话必须填写');
            Yii::$app->session->setFlash('list', $record);
            $this->goReference('product-package/' . $action);
        }

        return parent::preHandleField($record, $action);
    }

    /**
     * @inheritDoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        return parent::sufHandleField($record, $action, function ($record) {

            if (empty($record['id'])) {
                return $record;
            }

            $model = parent::model(self::$modelName);
            self::$_status = $model->_status;
            $record = $this->getFieldInfo($record, 'status');

            if (!empty($record['sale_rate'])) {

                $controller = $this->controller('product');
                $record['sale'] = $this->callMethod('saleLogic', 0, [$record], $controller);

                if (!$record['sale']) {
                    return $record;
                }

                $price = $record['price'];
                $rate = $record['sale_rate'];
                $sale_price = 0;

                switch ($record['sale_type']) {
                    case '1' :
                        $sale_price = intval($price - $rate) / 100;
                        break;

                    case '2' :
                        $sale_price = ($price - $price * ($rate / 100 / 100)) / 100;
                        break;
                }

                if ($sale_price > 0) {
                    $record['sale_price'] = $sale_price;
                } else {
                    $record['sale_price'] = $record['price'] / 100;
                }
            }

            return $record;
        });
    }

    /**
     * 填写套餐 - 编辑产品时弹出层
     *
     * @auth-pass-role 1
     */
    public function actionAjaxModalPackage()
    {
        $this->showForm();
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'product'],
                [
                    'left_table' => 'product',
                    'table' => 'product_upstream'
                ]
            ],
            'select' => [
                'product.title',
                'product.sale_type',
                'product.sale_rate',
                'product.sale_from',
                'product.sale_to',
                'product.state AS status',
                'product_upstream.name AS product_upstream_name',
                'product_package.*'
            ],
            'order' => [
                'product_package.state DESC',
                'ISNULL(product_package.sort), product_package.sort ASC',
                'product_package.update_time DESC'
            ]
        ]);
    }
}
