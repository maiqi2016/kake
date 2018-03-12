<?php

namespace backend\controllers;

/**
 * 活动故事管理
 *
 * @auth-inherit-except add edit front sort
 */
class ActivityStoryController extends GeneralController
{
    // 模型
    public static $modelName = 'ActivityStory';

    // 模型描述
    public static $modelInfo = '活动故事';

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
            'username' => [
                'table' => 'user',
                'elem' => 'input'
            ],
            'add_time' => [
                'elem' => 'input',
                'type' => 'date',
                'between' => true
            ],
            'state' => [
                'value' => self::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'username' => [
                'table' => 'user'
            ],
            'photo_preview_url' => [
                'title' => '照片',
                'img' => [
                    'pos' => 'right',
                    'max-width' => '500px'
                ],
                'width' => '128px'
            ],
            'story' => [
                'max-width' => '240px'
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
            'add_time'
        ];
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'user'],
                [
                    'table' => 'attachment',
                    'as' => 'photo',
                    'left_on_field' => 'photo_attachment_id'
                ]
            ],
            'select' => [
                'activity_story.*',
                'photo.deep_path AS photo_deep_path',
                'photo.filename AS photo_filename',
                'user.username'
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        // 生成封面图附件地址
        $record = $this->createAttachmentUrl($record, ['photo_attachment_id' => 'photo']);

        return parent::sufHandleField($record, $action);
    }
}
