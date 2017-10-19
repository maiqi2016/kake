<?php

namespace backend\controllers;

/**
 * 产品地区管理
 *
 * @auth-inherit-except front
 */
class ProductRegionController extends GeneralController
{
    // 模型
    public static $modelName = 'ProductRegion';

    // 模型描述
    public static $modelInfo = '产品地区';

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增产品地区',
                'value' => 'product-region/add',
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
                        'product_region.sort',
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
            'product_plate_id' => [
                'list_table' => 'product_plate',
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
            'product_plate_id' => [
                'list_table' => 'product_plate',
                'list_value' => 'name',
                'info',
                'code'
            ],
            'preview_url' => [
                'title' => '地区封面图',
                'img' => [
                    'pos' => 'left'
                ],
                'width' => '128px'
            ],
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
            'product_plate_id' => [
                'elem' => 'select',
                'list_table' => 'product_plate',
                'list_value' => 'name',
            ],

            'attachment_id' => [
                'hidden' => true
            ],
            'old_attachment_id' => [
                'value_key' => 'attachment_id',
                'hidden' => true
            ],
            'preview_url' => [
                'title' => '地区封面图',
                'elem' => 'img',
                'img_label' => 4,
                'upload_name' => 'upload'
            ],
            'upload' => [
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png',
                    'pic_sizes' => '336*234',
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
                'product_region.*',
                'attachment.deep_path AS deep_path',
                'attachment.filename AS filename'
            ],
            'order' => [
                'product_region.state DESC',
                'ISNULL(product_region.sort), product_region.sort ASC',
                'product_region.update_time DESC'
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
