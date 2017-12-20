<?php

namespace backend\controllers;

/**
 * 分销商活动奖品管理
 *
 * @auth-inherit-except front sort
 */
class ActivityProducerPrizeController extends GeneralController
{
    /**
     * @var string 模型
     */
    public static $modelName = 'ActivityProducerPrize';

    /**
     * @var string 模型描述
     */
    public static $modelInfo = '分销商活动奖品';

    /**
     * @var array Ubb/Html 互转钩子
     */
    public static $hookUbbAndHtml = ['description'];

    /**
     * 列表页全局操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增奖品',
                'value' => 'activity-producer-prize/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * 列表页单记录操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '查看产品',
                'value' => 'product/index',
                'params' => function ($record) {
                    return ['id' => $record['product_id']];
                },
                'level' => 'success',
                'icon' => 'link'
            ],
            [
                'text' => '查看抽奖码',
                'value' => 'activity-producer-code/index',
                'params' => function ($record) {
                    return ['activity_producer_prize_id' => $record['id']];
                },
                'level' => 'success',
                'icon' => 'link'
            ]
        ]);
    }

    /**
     * 列表页筛选器
     *
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * 列表页排序器
     *
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'id',
            'from',
            'to',
            'state'
        ];
    }

    /**
     * 列表页的字段辅助数据
     *
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'id' => [
                'code',
                'color' => 'default'
            ],
            'from' => 'code',
            'to' => 'code',
            'product_title' => [
                'title' => '产品标题',
                'max-width' => '250px'
            ],
            'product_name' => [
                'title' => '产品名称'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * 编辑页字段辅助数据
     *
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        return [
            'product_id' => [
                'readonly' => true,
                'same_row' => true
            ],
            'select_product' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择产品',
                'script' => '$.showPage("product.list-product", {state: 1})'
            ],
            'from' => [
                'title' => '活动开始日期',
                'type' => 'date'
            ],
            'to' => [
                'title' => '活动结束日期',
                'type' => 'date'
            ],
            'description' => [
                'elem' => 'ckeditor',
                'title' => '奖品描述',
                'tip' => '必须填写',
                'pos' => 'left',
                'width' => 414,
                'height' => 400
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * 列表页查询构建器
     *
     * @inheritdoc
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
                'activity_producer_prize.*',
                'product.title AS product_title',
                'product_upstream.name AS product_name',
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->sourceJs = ['ckeditor/ckeditor'];

        return parent::beforeAction($action);
    }
}