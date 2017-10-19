<?php

namespace backend\controllers;

/**
 * 产品板块管理
 *
 * @auth-inherit-except front
 */
class ProductPlateController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductPlate';

    // 模型描述
    public static $modelInfo = '产品板块';

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增产品板块',
                'value' => 'product-plate/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * 微操作
     *
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'alt' => '排序',
                'level' => 'default',
                'icon' => 'sort-by-attributes',
                'type' => 'script',
                'value' => '$.sortField',
                'params' => function ($record) {
                    return [
                        'product_plate.sort',
                        $record['id'],
                        $record['sort']
                    ];
                },
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function indexFilter()
    {
        return [
            'id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'name' => 'input',
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexSorter()
    {
        return [
            'id',
            'name',
            'sort'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'name' => [
                'max-width' => '350px'
            ],
            'preview_url' => [
                'title' => '板块封面图',
                'img' => [
                    'pos' => 'right'
                ],
                'width' => '128px'
            ],
            'update_time',
            'sort' => 'code',
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        return [
            'name' => [
                'placeholder' => '32个字以内'
            ],

            'attachment_id' => [
                'hidden' => true
            ],
            'old_attachment_id' => [
                'value_key' => 'attachment_id',
                'hidden' => true
            ],
            'preview_url' => [
                'title' => '板块封面图',
                'elem' => 'img',
                'img_label' => 4,
                'upload_name' => 'upload'
            ],
            'upload' => [
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png',
                    'pic_sizes' => '188*380',
                    'max_size' => 512
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
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'attachment']
            ],
            'select' => [
                'product_plate.*',
                'attachment.deep_path AS deep_path',
                'attachment.filename AS filename'
            ],
            'order' => [
                'product_plate.state DESC',
                'ISNULL(product_plate.sort), product_plate.sort ASC',
                'product_plate.update_time DESC'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function editCondition()
    {
        return self::indexCondition();
    }

    /**
     * @inheritDoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        // 生成封面图附件地址
        $record = $this->createAttachmentUrl($record, ['attachment_id']);

        return parent::sufHandleField($record, $action);
    }

    /**
     * @inheritDoc
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
