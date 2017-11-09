<?php

namespace backend\controllers;

use backend\components\ViewHelper;
use common\components\Helper;

/**
 * 分销商分组成员
 *
 * @auth-inherit-except front
 */
class ProducerGroupMemberController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerGroupMember';

    // 模型描述
    public static $modelInfo = '分销商分组成员';

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增分销商分组成员',
                'value' => 'producer-group-member/add',
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
            'username' => [
                'elem' => 'input',
                'table' => 'user'
            ],
            'producer_group_id' => [
                'title' => '分组',
                'list_table' => 'producer_group',
                'list_value' => 'name',
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'username' => [
                'code',
                'table' => 'user',
                'color' => 'default'
            ],
            'group_name' => [
                'title' => '分组',
                'code',
                'color' => 'primary'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        return [
            'producer_id' => [
                'readonly' => true,
                'same_row' => true,
                'label' => 2,
            ],
            'select_producer' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择分销商',
                'script' => '$.showPage("producer-setting.list", {state: 1})'
            ],
            'producer_group_id' => [
                'readonly' => true,
                'same_row' => true,
                'label' => 2,
            ],
            'select_producer_group' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择分组',
                'script' => '$.showPage("producer-group.list", {state: 1})'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                [
                    'table' => 'user',
                    'left_on_field' => 'producer_id'
                ],
                [
                    'table' => 'producer_group',
                    'left_on_field' => 'producer_group_id'
                ]
            ],
            'select' => [
                'producer_group_member.*',
                'user.username',
                'producer_group.name AS group_name'
            ]
        ]);
    }
}
