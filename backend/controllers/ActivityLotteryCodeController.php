<?php

namespace backend\controllers;

/**
 * 活动抽奖码管理
 *
 * @auth-inherit-except add edit front sort
 */
class ActivityLotteryCodeController extends GeneralController
{
    // 模型
    public static $modelName = 'ActivityLotteryCode';

    // 模型描述
    public static $modelInfo = '活动抽奖码';

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
    public static function indexOperationForm()
    {
        return [
            [
                'text' => '导出Excel',
                'type' => 'attr',
                'level' => 'success condition-global-event',
                'params' => [
                    'event' => 'export'
                ],
                'icon' => 'save'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'nickname' => 'input',
            'real_name' => 'input',
            'phone' => 'input',
            'company' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'subscribe' => [
                'value' => parent::SELECT_KEY_ALL
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
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'nickname',
            'real_name',
            'phone',
            'company' => 'info',
            'code' => [
                'empty',
                'code'
            ],
            'subscribe' => [
                'code',
                'color' => 'auto',
                'info'
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
    public static function indexExportAssist()
    {
        return [
            'id',
            'nickname',
            'real_name',
            'phone',
            'company_info' => '公司名称',
            'code' => '抽奖码',
            'state_info' => '期号'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
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
                'id DESC'
            ]
        ]);
    }

    /**
     * 导出数据
     */
    public function actionIndexExport()
    {
        $this->exportList('抽奖码记录.' . date('Ymd'));
    }
}
