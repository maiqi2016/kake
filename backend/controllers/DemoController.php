<?php

namespace backend\controllers;

/**
 * 示例控制器
 *
 * @auth-inherit-except front sort
 */
class DemoController extends GeneralController
{
    /**
     * @var string 模型
     */
    public static $modelName = 'Demo';

    /**
     * @var string 模型描述
     */
    public static $modelInfo = '示例';

    /**
     * @var array Ubb/Html 互转钩子
     */
    public static $hookUbbAndHtml = [];

    /**
     * @var array 价格字段优化钩子
     */
    public static $hookPriceNumber = [];

    /**
     * @var array 双向时间选择钩子
     */
    public static $hookDateSectionDouble = [];

    /**
     * @var array 自定义逻辑钩子
     */
    public static $hookLogic = [];

    /**
     * 列表页全局操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增',
                'value' => 'demo/add',
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
                'text' => '二维码',
                'type' => 'script',
                'value' => '$.showQrCode',
                'params' => ['link_url'],
                'level' => 'success',
                'icon' => 'qrcode'
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
            'join' => [],
            'select' => []
        ]);
    }

    /**
     * 编辑页查询构建器
     *
     * @inheritdoc
     */
    public function editCondition()
    {
        return [
            'join' => [],
            'select' => [],
        ];
    }

    /**
     * 数据写入前钩子
     *
     * @inheritdoc
     */
    public function preHandleField($record, $action = null)
    {
        return parent::preHandleField($record, $action);
    }

    /**
     * 数据展示前钩子
     *
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        return parent::sufHandleField($record, $action);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }
}