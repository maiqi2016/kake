<?php

namespace backend\controllers;

/**
 * 分销商活动签到管理
 *
 * @auth-inherit-except add edit front sort
 */
class ActivityProducerSignController extends GeneralController
{
    /**
     * @var string 模型
     */
    public static $modelName = 'ActivityProducerSign';

    /**
     * @var string 模型描述
     */
    public static $modelInfo = '分销商活动签到';

    /**
     * 列表页单记录操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return null;
    }

    /**
     * 列表页筛选器
     *
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'user_id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'add_time' => [
                'elem' => 'input',
                'type' => 'date',
                'between' => true
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
            'user_id',
            'add_time',
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
            'user_id' => 'code',
            'user' => [
                'title' => '用户',
                'code'
            ],
            'add_time' => [
                'title' => '签到时间'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
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
                ['table' => 'user']
            ],
            'select' => [
                'activity_producer_sign.*',
                'user.username AS user'
            ]
        ]);
    }
}