<?php

namespace backend\controllers;

use Yii;

/**
 * 产品分销管理
 *
 * @auth-inherit-except front
 */
class ProductProducerController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductProducer';

    // 模型描述
    public static $modelInfo = '产品分销';

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['commission'];

    /**
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增产品分销',
                'value' => 'product-producer/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '产品',
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
                        'product-producer.sort',
                        $record['id'],
                        $record['sort']
                    ];
                },
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'product_id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'from_sales' => [
                'elem' => 'input',
                'equal' => true
            ],
            'to_sales' => [
                'elem' => 'input',
                'equal' => true
            ],
            'type' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'commission' => [
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
    public static function indexAssist()
    {
        return [
            'product_id' => [
                'code',
                'color' => 'default'
            ],
            'title' => [
                'title' => '产品'
            ],
            'from_sales' => [
                'tpl' => '≥ %s 个'
            ],
            'to_sales' => [
                'tpl' => '≤ %s 个',
                'not_set_info' => '<span class="not-set">+∞</span>'
            ],
            'type' => [
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'primary'
                ]
            ],
            'commission' => [
                'code',
                'price',
                'color' => function ($item) {
                    return !$item['type'] ? 'default' : 'primary';
                },
                'tpl' => function ($item) {
                    return !$item['type'] ? '￥%s' : '%s%%';
                }
            ],
            'sort' => 'code',
            'add_time' => 'tip',
            'update_time' => 'tip',
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'danger',
                    1 => 'info',
                    2 => 'default'
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'from_sales',
            'to_sales',
            'commission',
            'sort'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        return [
            'type' => [
                'elem' => 'select',
                'class' => 'product_producer-type',
                'change' => '$.productProducerStart()'
            ],
            'product_id' => [
                'same_row' => true,
                'class' => 'product_producer-product_id',
                'change' => '$.productProducerStart()'
            ],
            'select_product' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择产品',
                'script' => '$.showPage("product.list", {state: 1})'
            ],

            'from_sales' => [
                'placeholder' => '请输入整数',
                'tip' => '前一个档次的结束销量 + 1'
            ],
            'to_sales' => [
                'placeholder' => '请输入整数',
                'tip' => '留空或为零表示无限大'
            ],
            'commission' => [
                'placeholder' => '保留到小数点后两位'
            ],
            'sort' => [
                'placeholder' => '大于零的整数，越小越靠前'
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
        if (in_array($action, [
            'add',
            'edit'
        ])) {
            if (!empty($record['to_sales']) && $record['to_sales'] <= $record['from_sales']) {
                $this->goReference($this->getControllerName($action), [
                    'warning' => '结束销量必须大于开始销量',
                    'list' => $record
                ]);
            }

            if (!empty($record['type']) && ($record['commission'] <= 0 || $record['commission'] >= 100)) {
                $this->goReference($this->getControllerName($action), [
                    'warning' => '百分比分佣值请填写 0 ~ 100 之间的数',
                    'list' => $record
                ]);
            }
        }

        return parent::preHandleField($record, $action);
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return [
            'join' => [
                ['table' => 'product']
            ],
            'select' => [
                'product_producer.*',
                'product.title'
            ],
            'where' => [
                ['product.state' => 1]
            ],
            'order' => [
                'product_producer.state DESC',
                'ISNULL(product_producer.sort), product_producer.sort ASC',
                'product_producer.update_time DESC'
            ]
        ];
    }

    /**
     * 获取下一档次起始销量
     *
     * @access public
     * @auth-pass-all
     *
     * @param integer $product_id
     * @param integer $type
     */
    public function actionAjaxGetFromSales($product_id, $type)
    {
        $where = [
            [
                'product_id' => $product_id,
                'type' => $type
            ]
        ];

        $record = $this->service(parent::$apiDetail, [
            'table' => 'product_producer',
            'select' => [
                'id',
                'from_sales',
                'to_sales'
            ],
            'where' => [
                [
                    'sub' => [
                        'select' => ['MAX(from_sales) AS from_sales'],
                        'where' => $where
                    ],
                    'tpl' => "['from_sales' => {SUB_QUERY}]"
                ],
                current($where)
            ],
        ]);

        if (empty($record['id'])) {
            $this->success(1);
        }

        if (empty($record['to_sales'])) {
            $this->fail('该产品的前一个档次已经收尾，请务必先修改它再继续新增。');
        }

        $this->success($record['to_sales'] + 1);
    }
}
