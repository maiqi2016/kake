<?php

namespace backend\controllers;

use backend\components\ViewHelper;
use Oil\src\Helper;
use yii\helpers\Url;
use Yii;

/**
 * 广告管理
 *
 * @auth-inherit-except front
 */
class AdController extends GeneralController
{
    // 模型
    public static $modelName = 'Ad';

    // 模型描述
    public static $modelInfo = '广告';

    /**
     * @var array Hook
     */
    public static $hookDateSectionDouble = [''];

    /**
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增广告',
                'value' => 'ad/add',
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
                'text' => '二维码',
                'type' => 'script',
                'value' => '$.showQrCode',
                'params' => ['link_url'],
                'level' => 'success',
                'icon' => 'qrcode'
            ],
            [
                'alt' => '排序',
                'level' => 'default',
                'icon' => 'sort-by-attributes',
                'type' => 'script',
                'value' => '$.sortField',
                'params' => function ($record) {
                    return [
                        'ad.sort',
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
            'type' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'target' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'type',
            'from',
            'to',
            'sort'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'type' => [
                'code',
                'info'
            ],
            'target' => [
                'code',
                'info'
            ],
            'remark',
            'from',
            'to',
            'sort' => 'code',
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ],
            'preview_url' => [
                'img' => [
                    'pos' => 'left'
                ],
                'width' => '128px',
            ]
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
                'value' => 1
            ],
            'target' => [
                'elem' => 'select',
                'value' => 1,
                'tip' => [
                    '_self' => '当前窗口打开',
                    '_blank' => '新窗口打开',
                ],
            ],
            'url' => [
                'label' => 4,
                'tip' => [
                    '格式1' => 'site/index 表示网站域名 + ' . Yii::$app->params['frontend_url']. Url::toRoute(['site/index']),
                    '格式2' => '以 http(s):// 开头的完整地址串',
                    '格式3' => '脚本代码，如：javascript:void(null)'
                ],
            ],
            'remark' => [
                'elem' => 'textarea',
                'placeholder' => '128个字以内'
            ],
            'from' => [
                'type' => 'datetime-local',
                'label' => 3,
                'tip' => [
                    'AM' => '上午',
                    'PM' => '下午'
                ]
            ],
            'to' => [
                'type' => 'datetime-local',
                'label' => 3,
                'tip' => [
                    'AM' => '上午',
                    'PM' => '下午'
                ]
            ],
            'standard_size' => [
                'elem' => 'text',
                'class' => 'bg-info',
                'title' => '附件标准尺寸',
                'html' => true,
                'value' => '首页焦点：750 × 500<br>首页广告：750 × 160<br>分销焦点：648 × 432<br>分销广告：750 × 253',
            ],

            'attachment_id' => [
                'hidden' => true
            ],
            'old_attachment_id' => [
                'value_key' => 'attachment_id',
                'hidden' => true
            ],
            'preview_url' => [
                'elem' => 'img',
                'img_label' => 4,
                'upload_name' => 'upload'
            ],
            'upload' => [
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png,gif',
                    'pic_sizes' => '600-750*160-500',
                    'max_size' => 2048
                ],
                'preview_name' => 'preview_url',
                'field_name' => 'attachment_id'
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
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'attachment']
            ],
            'select' => [
                'attachment.deep_path',
                'attachment.filename',
                'ad.*'
            ],
            'order' => [
                'ad.state DESC',
                'ISNULL(ad.sort), ad.sort ASC',
                'ad.update_time DESC'
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function editCondition()
    {
        $condition = $this->indexCondition();
        unset($condition['order']);

        return $condition;
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        $record = $this->createAttachmentUrl($record, 'attachment_id');
        $record = $this->createLinkUrl($record, 'url');

        return parent::sufHandleField($record, $action);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->sourceJs = [
            'jquery.ajaxupload',
            '/node_modules/cropper/dist/cropper.min'
        ];
        $this->sourceCss = ['/node_modules/cropper/dist/cropper.min'];

        return parent::beforeAction($action);
    }
}
