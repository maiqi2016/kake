<?php

namespace backend\controllers;

/**
 * 分销商活动抽奖码管理
 *
 * @auth-inherit-except add edit front sort
 */
class ActivityProducerCodeController extends GeneralController
{
    /**
     * @var string 模型
     */
    public static $modelName = 'ActivityProducerCode';

    /**
     * @var string 模型描述
     */
    public static $modelInfo = '分销商活动抽奖码';

    /**
     * 列表页单记录操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return [
            [
                'text' => '查看奖品',
                'value' => 'activity-producer-prize/index',
                'params' => ['id'],
                'level' => 'success',
                'icon' => 'link'
            ]
        ];
    }

    /**
     * 列表页筛选器
     *
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'activity_producer_prize_id' => [
                'title' => '奖品ID',
                'elem' => 'input',
                'equal' => true
            ],
            'producer' => [
                'elem' => 'input',
                'title' => '分销商',
                'table' => 'producer',
                'field' => 'username'
            ],
            'user' => [
                'elem' => 'input',
                'title' => '用户',
                'table' => 'user',
                'field' => 'username'
            ],
            'from_user' => [
                'elem' => 'input',
                'title' => '接受邀请用户',
                'table' => 'from_user',
                'field' => 'username'
            ],
            'phone' => 'input',
            'code' => [
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
            'activity_producer_prize_id',
            'code',
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
            'activity_producer_prize_id' => [
                'title' => '奖品ID',
                'code',
                'color' => 'default'
            ],
            'producer' => [
                'title' => '分销商',
            ],
            'user' => [
                'title' => '用户'
            ],
            'from_user' => [
                'title' => '接受邀请用户'
            ],
            'phone',
            'code' => [
                'code',
                'color' => 'default'
            ],
            'add_time',
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
                ['table' => 'user'],
                [
                    'table' => 'user',
                    'as' => 'from_user',
                    'left_on_field' => 'from_user_id'
                ],
                [
                    'table' => 'user',
                    'as' => 'producer',
                    'left_on_field' => 'producer_id'
                ],
            ],
            'select' => [
                'activity_producer_code.*',
                'user.username AS user',
                'from_user.username AS from_user',
                'producer.username AS producer'
            ]
        ]);
    }
}