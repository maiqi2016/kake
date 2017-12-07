<?php

namespace backend\controllers;

/**
 * 活动兑奖管理
 *
 * @auth-inherit-except add edit front sort
 */
class ActivityWinningCodeController extends GeneralController
{
    // 模型
    public static $modelName = 'ActivityWinningCode';

    // 模型描述
    public static $modelInfo = '活动兑奖';

    /**
     * @var array Hook
     */
    public static $hookLogic = ['check'];

    /**
     * @var array Field
     */
    public static $_check = [
        0 => '未核领',
        1 => '已核领'
    ];

    /**
     * 是否核对
     *
     * @param array $record
     *
     * @return boolean
     */
    public static function checkLogic($record)
    {
        return !empty($record['openid']);
    }

    /**
     * 是否核对反向逻辑
     *
     * @param integer $index
     *
     * @return array
     */
    public static function checkReverseWhereLogic($index)
    {
        $indexes = [
            0 => [
                ['activity_winning_code.openid' => null],
            ],
            1 => [
                [
                    'is not',
                    'activity_winning_code.openid',
                    null
                ],
            ]
        ];

        return isset($indexes[$index]) ? $indexes[$index] : [];
    }

    /**
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'nickname' => 'input',
            'check' => [
                'title' => '领取状态',
                'value' => self::SELECT_KEY_ALL
            ],
            'winning' => [
                'value' => self::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'nickname',
            'code' => [
                'empty',
                'code'
            ],
            'winning' => [
                'title' => '中奖状况',
                'info',
                'code',
                'color' => [
                    0 => 'default',
                    1 => 'success'
                ]
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
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'winning',
            'add_time'
        ];
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'order' => [
                'id ASC'
            ]
        ]);
    }
}
