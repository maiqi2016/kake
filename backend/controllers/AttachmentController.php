<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 附件管理
 *
 * @auth-inherit-except add edit front sort
 */
class AttachmentController extends GeneralController
{
    // 模型
    public static $modelName = 'Attachment';

    // 模型描述
    public static $modelInfo = '附件';

    /**
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return [
            [
                'text' => '下载附件',
                'value' => 'download',
                'icon' => 'download-alt',
                'params' => function ($item) {
                    return ['file' => base64_encode(current($item['preview_url']))];
                },
                'show_condition' => function ($item) {
                    return $item['state'] != 2;
                }
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'update_time',
            'state'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'deep_path' => 'code',
            'filename',
            'add_time',
            'update_time',
            'preview_url' => [
                'img' => [
                    'pos' => 'left',
                    'max-width' => '500px'
                ],
                'width' => '128px',
                'not_set_info' => '<span class="not-set">(Deleted)</span>'
            ],
            'state' => [
                'code',
                'color' => [
                    0 => 'warning',
                    1 => 'success',
                    2 => 'default'
                ],
                'info'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (!empty($record['filename'])) {
            $suffix = Helper::getSuffix($record['filename']);
            $suffixes = [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'svg'
            ];
            if (in_array($suffix, $suffixes) && $record['state'] != 2) {
                $record = $this->createAttachmentUrl($record, 'id');
            }
        }

        return parent::sufHandleField($record, $action);
    }

    /**
     * 附件下载
     *
     * @param string $file
     */
    public function actionDownload($file)
    {
        $file = base64_decode($file);
        Yii::$app->download->remoteDownload($file);
    }
}
