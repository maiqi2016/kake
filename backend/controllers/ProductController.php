<?php

namespace backend\controllers;

use backend\components\ViewHelper;
use Oil\src\Helper;
use Yii;
use yii\helpers\Url;

/**
 * 产品管理
 *
 * @auth-inherit-except front
 */
class ProductController extends GeneralController
{
    /**
     * @var string 模型
     */
    public static $modelName = 'Product';

    /**
     * @var string 模型描述
     */
    public static $modelInfo = '产品';

    /**
     * @var string 添加操作个性化 api
     */
    public static $apiGeneralAdd = 'product.add-product';

    /**
     * @var string 编辑操作个性化 api
     */
    public static $apiGeneralUpdate = 'product.update-product';

    /**
     * @var string 产品列表弹窗标题
     */
    public static $ajaxModalListTitle = '选择产品';

    /**
     * @var string 分销产品列表弹窗标题
     */
    public static $ajaxModalListProductTitle = '选择分销产品';

    /**
     * @var array 分佣策略
     */
    public static $type = [
        0 => 'fixed',
        1 => 'percent'
    ];

    /**
     * @var array Ubb/Html 互转钩子
     */
    public static $hookUbbAndHtml = [
        'cost',
        'recommend',
        'use',
        'back'
    ];

    /**
     * @var array 价格字段优化钩子
     */
    public static $hookPriceNumber = [
        'sale_rate',
        'sale_price'
    ];

    /**
     * @var array 双向时间选择钩子
     */
    public static $hookDateSectionDouble = ['sale'];

    /**
     * @var array 自定义逻辑钩子
     */
    public static $hookLogic = ['sale'];

    /**
     * @var array 字段枚举
     */
    public static $_sale = [
        0 => '否',
        1 => '是'
    ];

    /**
     * 是否打折逻辑
     *
     * @param array $record
     *
     * @return boolean
     */
    public static function saleLogic($record)
    {
        if (!isset($record['sale_from']) || !isset($record['sale_to'])) {
            return false;
        }
        $from = strtotime($record['sale_from']);
        $to = strtotime($record['sale_to']);

        return (!empty($record['sale_rate']) && $from < TIME && $to > TIME) ? 1 : 0;
    }

    /**
     * 是否打折反向逻辑
     *
     * @param integer $index
     *
     * @return array
     */
    public static function saleReverseWhereLogic($index)
    {
        $now = date('Y-m-d H:i:s', TIME);
        $indexes = [
            0 => [
                [
                    'or',
                    ['product.sale_rate' => null],
                    ['product.sale_rate' => 0],
                    [
                        '>',
                        'product.sale_from',
                        $now
                    ],
                    [
                        '<',
                        'product.sale_to',
                        $now
                    ]
                ]
            ],
            1 => [
                [
                    '>',
                    'product.sale_rate',
                    0
                ],
                [
                    '<',
                    'product.sale_from',
                    $now
                ],
                [
                    '>',
                    'product.sale_to',
                    $now
                ]
            ]
        ];

        return isset($indexes[$index]) ? $indexes[$index] : [];
    }

    /**
     * 产品列表页全局操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增产品',
                'value' => 'product/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * 产品列表弹窗页全局操作按钮
     *
     * @inheritdoc
     */
    public static function ajaxModalListOperations()
    {
        return [
            [
                'text' => '提交选择',
                'type' => 'script',
                'value' => '$.modalRadioValueToInput("radio", "product_id")',
                'icon' => 'flag'
            ]
        ];
    }

    /**
     * 分销产品列表弹窗页全局操作按钮
     *
     * @inheritdoc
     */
    public static function ajaxModalListProductOperations()
    {
        return self::ajaxModalListOperations();
    }

    /**
     * 产品列表页单记录操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '套餐',
                'value' => 'product-package/index',
                'level' => 'info',
                'icon' => 'link',
                'params' => function ($record) {
                    return ['product_id' => $record['id']];
                },
            ],
            [
                'text' => '分销',
                'value' => 'product-producer/index',
                'level' => 'info',
                'icon' => 'link',
                'params' => function ($record) {
                    return ['product_id' => $record['id']];
                }
            ],
            [
                'br' => true,
                'text' => '二维码',
                'type' => 'script',
                'value' => '$.showQrCode',
                'params' => ['link_url'],
                'level' => 'success',
                'icon' => 'qrcode'
            ],
            [
                'alt' => '复制产品链接',
                'level' => 'default copy',
                'icon' => 'copy',
                'type' => 'attr',
                'params' => function ($record) {
                    return ['data-clipboard-text' => $record['link_url']];
                }
            ],
            [
                'alt' => '排序',
                'level' => 'default',
                'icon' => 'sort-by-attributes',
                'type' => 'script',
                'value' => '$.sortField',
                'params' => function ($record) {
                    return [
                        'product.sort',
                        $record['id'],
                        $record['sort']
                    ];
                },
            ]
        ]);
    }

    /**
     * 分销产品列表弹窗页单记录操作按钮
     *
     * @inheritdoc
     */
    public static function ajaxModalListProductOperation()
    {
        return [
            [
                'text' => '二维码',
                'type' => 'script',
                'value' => '$.showQrCode',
                'params' => function ($record) {
                    $url = Url::toRoute([
                        'detail/index',
                        'id' => $record['id']
                    ]);
                    $url = Yii::$app->params['frontend_url'] . $url;

                    return [$url];
                },
                'level' => 'success',
                'icon' => 'qrcode'
            ]
        ];
    }

    /**
     * 产品列表页筛选器
     *
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'title' => 'input',
            'product_upstream_name' => [
                'elem' => 'input',
                'table' => 'product_upstream',
                'field' => 'name',
                'title' => '上游名称'
            ],
            'product_region_id' => [
                'table' => 'product_upstream',
                'list_table' => 'product_region',
                'list_value' => 'name',
                'value' => parent::SELECT_KEY_ALL
            ],
            'classify' => [
                'table' => 'product_upstream',
                'value' => parent::SELECT_KEY_ALL
            ],
            'sale_type' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'sale' => [
                'title' => '打折中',
                'value' => parent::SELECT_KEY_ALL
            ],
            'stock' => [
                'elem' => 'input',
                'equal' => true
            ],
            'night_times' => [
                'elem' => 'input',
                'equal' => true
            ],
            'manifestation' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * 产品列表弹窗页筛选器
     *
     * @inheritdoc
     */
    public static function ajaxModalListFilter()
    {
        return [
            'title' => 'input',
            'product_upstream_name' => [
                'elem' => 'input',
                'table' => 'product_upstream',
                'field' => 'name',
                'title' => '上游名称'
            ],
            'classify' => [
                'table' => 'product_upstream',
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * 分销产品列表弹窗页筛选器
     *
     * @inheritdoc
     */
    public static function ajaxModalListProductFilter()
    {
        return self::ajaxModalListFilter();
    }

    /**
     * 产品列表页排序器
     *
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'id',
            'sort',
            'classify' => [
                'table' => 'product_upstream'
            ]
        ];
    }

    /**
     * 产品列表弹窗页排序器
     *
     * @inheritdoc
     */
    public static function ajaxModalListSorter()
    {
        return self::indexSorter();
    }

    /**
     * 产品列表页的字段辅助数据
     *
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'id' => [
                'code',
                'color' => 'default'
            ],
            'title' => [
                'max-width' => '250px'
            ],
            'product_upstream_name' => [
                'table' => 'product_upstream',
                'field' => 'name',
                'title' => '上游名称',
                'tip'
            ],
            'product_region_id' => [
                'table' => 'product_upstream',
                'list_table' => 'product_region',
                'list_value' => 'name',
                'info',
                'code'
            ],
            'classify' => [
                'table' => 'product_upstream',
                'code',
                'info',
                'color' => [
                    0 => 'primary',
                    1 => 'success',
                    2 => 'info',
                    3 => 'default'
                ]
            ],
            'sale' => [
                'title' => '打折中',
                'info',
                'empty',
                'code',
                'color' => 'success'
            ],
            'stock' => 'tip',
            'night_times',
            'manifestation' => [
                'code',
                'info'
            ],
            'virtual_sales' => 'tip',
            'real_sales' => 'tip',
            'share_times' => 'tip',
            'referral_link' => [
                'link',
                'url_info' => 'Article',
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
     * 产品列表弹窗页的字段辅助数据
     *
     * @inheritdoc
     */
    public static function ajaxModalListAssist()
    {
        return [
            'id' => 'code',
            'title',
            'classify' => [
                'table' => 'product_upstream',
                'code',
                'info',
                'color' => [
                    0 => 'primary',
                    1 => 'success',
                    2 => 'info',
                    3 => 'default'
                ]
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * 分销产品列表弹窗页的字段辅助数据
     *
     * @inheritdoc
     */
    public static function ajaxModalListProductAssist()
    {
        return [
            'id' => 'code',
            'product_upstream_name' => [
                'title' => '上游名称',
                'max-width' => '250px'
            ],
            'title' => [
                'max-width' => '300px'
            ],
            'producer' => [
                'hidden',
                'table' => 'product_producer',
                'foreign_key' => 'product_id',
                'service_api' => 'product.producer-list'
            ],
            'commission_table_fixed' => [
                'html',
                'title' => '固定额分佣'
            ],
            'commission_table_percent' => [
                'html',
                'title' => '百分比分佣'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * 分销产品列表页的字段辅助数据 - 辅助分销产品排序
     *
     * @inheritdoc
     */
    public static function sortProductAssist()
    {
        return [
            'id' => 'code',
            'product_upstream_name' => [
                'title' => '上游名称'
            ],
            'title'
        ];
    }

    /**
     * 分销产品列表页的字段辅助数据 - 辅助获取分销信息
     *
     * @inheritdoc
     */
    public static function listProductAssist()
    {
        return [
            'producer' => [
                'hidden',
                'table' => 'product_producer',
                'foreign_key' => 'product_id',
                'service_api' => 'product.producer-list',
                'service_params' => ['where' => []]
            ]
        ];
    }

    /**
     * 产品编辑页字段辅助数据
     *
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        $model = parent::model('ProductDescription');
        $description = $model->attributeLabels();

        return [
            'title' => [
                'placeholder' => '64个字以内',
                'label' => 4
            ],
            'product_upstream_id' => [
                'readonly' => true,
                'same_row' => true,
                'table' => 'product_upstream'
            ],
            'select_hotel' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择上游',
                'script' => '$.showPage("product-upstream.list", {state: 1})'
            ],
            'sale_type' => [
                'elem' => 'select',
                'same_row' => true
            ],
            'sale_rate' => [
                'title' => false,
                'placeholder' => '填写后将以折后价格售卖',
                'tip' => [
                    '此处填写的是折扣掉的值',
                    '',
                    '<span class=text-danger>打折效果对所有套餐生效，保留到小数点后两位</span>',
                    '固定折扣价' => '按实际需要折扣的金额额度直接填写',
                    '百分比折扣' => '如需打 85 折则填写 15 (100-85)',
                ]
            ],
            'sale_from' => [
                'type' => 'datetime-local',
                'label' => 3,
                'tip' => [
                    '`折扣值` 字段填写后有效',
                    '',
                    'AM' => '上午',
                    'PM' => '下午'
                ]
            ],
            'sale_to' => [
                'type' => 'datetime-local',
                'label' => 3,
                'tip' => [
                    '必须晚于开始时间',
                    '',
                    'AM' => '上午',
                    'PM' => '下午'
                ]
            ],

            'package_ids' => [
                'hidden' => true
            ],
            'old_package_ids' => [
                'value_key' => 'package_ids',
                'hidden' => true
            ],
            // format               完成标签表单后将值格式化成该模板返回给 JS 处理
            // table                列表数据和写入数据的目标表
            // foreign_key          列表数据和写入数据对应当前表的外键字段
            // handler_controller   列表数据后处理或写入数据的前处理对应方法所在的控制器
            // service_api          列表数据的接口
            'package' => [
                'title' => '套餐',
                'elem' => 'tag',
                'label' => 10,
                'format' => '{name} (¥{price})',
                'field_name' => 'package_ids',
                'table' => 'product_package',
                'foreign_key' => 'product_id',
                'service_api' => 'product.package-list'
            ],
            'add_package' => [
                'title' => '',
                'elem' => 'button',
                'value' => '添加套餐',
                'script' => '$.showPage("product-package.package")'
            ],

            'stock' => [
                'value' => 0,
                'placeholder' => '抢购商品硬性库存'
            ],
            'virtual_sales' => [
                'value' => rand(99, 999),
                'tip' => [
                    '前台显示销量规则',
                    '',
                    '虚拟销量 > 真实销量' => '虚拟销量 + 真实销量',
                    '虚拟销量 ≤ 真实销量' => '真实销量'
                ]
            ],
            'night_times' => [
                'tip' => '留空时在详情页将不显示该数据',
                'placeholder' => '套餐跨晚次'
            ],
            'manifestation' => [
                'elem' => 'select',
                'value' => 0,
                'tip' => '针对首页显示的位置'
            ],

            'attachment_cover' => [
                'hidden' => true
            ],
            'old_attachment_cover' => [
                'value_key' => 'attachment_cover',
                'hidden' => true
            ],
            'cover_preview_url' => [
                'title' => '封面图预览',
                'elem' => 'img',
                'upload_name' => 'upload_cover'
            ],
            'upload_cover' => [
                'title' => '',
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png',
                    'pic_sizes' => '750*500',
                    'max_size' => 512
                ],
                'preview_name' => 'cover_preview_url',
                'field_name' => 'attachment_cover'
            ],

            // < 存储当前附件 item >
            'attachment_ids' => [
                'hidden' => true
            ],
            // < 存储旧时附件 item >
            // value_key    标示该值和指定 key 的值保持一致
            'old_attachment_ids' => [
                'value_key' => 'attachment_ids',
                'hidden' => true
            ],
            // < 附件预览 item >
            // upload_name  < 上传附件 item > 的 name 值
            'slave_preview_url' => [
                'title' => '次要图预览',
                'elem' => 'img',
                'upload_name' => 'upload_slave'
            ],
            // < 上传附件 item >
            // tag              标记 (用于在控制器中寻找 rules 在后), 单控制器不重复出现
            // rules            标示上传附件的规范 (实为 Oil\src\Upload 组件的参数)
            // preview_name     < 附件预览 item > 的 name 值
            // field_name       < 存储当前附件 item > 的 name 值
            // multiple         是否支持多附件
            'upload_slave' => [
                'title' => '',
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png',
                    'pic_sizes' => '750*500',
                    'max_size' => 512
                ],
                'preview_name' => 'slave_preview_url',
                'field_name' => 'attachment_ids',
                'multiple' => true
            ],

            'cost' => [
                'elem' => 'ckeditor',
                'title' => $description['cost'],
                'tip' => '必须填写',
                'pos' => 'left',
                'width' => 414
            ],
            'recommend' => [
                'elem' => 'ckeditor',
                'title' => $description['recommend'],
                'tip' => '必须填写',
                'pos' => 'left',
                'width' => 414
            ],
            'use' => [
                'elem' => 'ckeditor',
                'title' => $description['use'],
                'tip' => '必须填写',
                'pos' => 'left',
                'width' => 414
            ],
            'back' => [
                'elem' => 'ckeditor',
                'title' => $description['back'],
                'tip' => '必须填写',
                'pos' => 'left',
                'width' => 414
            ],

            'sort' => [
                'placeholder' => '大于零的整数，越小越靠前'
            ],
            'referral_link' => [
                'placeholder' => '如：微信发文链接, 可留空',
                'label' => 6
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * 产品列表页查询构建器
     *
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return [
            'join' => [
                [
                    'table' => 'attachment',
                    'as' => 'master',
                    'left_on_field' => 'attachment_cover'
                ],
                ['table' => 'product_upstream']
            ],
            'select' => [
                'master.deep_path AS master_deep_path',
                'master.filename AS master_filename',
                'product_upstream.name AS product_upstream_name',
                'product_upstream.product_region_id',
                'product_upstream.classify',
                'product.*'
            ],
            'order' => [
                'product.state DESC',
                'ISNULL(product.sort), product.sort ASC',
                'product.update_time DESC'
            ]
        ];
    }

    /**
     * 产品列表弹窗页查询构建器
     *
     * @inheritdoc
     */
    public function ajaxModalListCondition()
    {
        return self::indexCondition();
    }

    /**
     * 分销产品列表弹窗页查询构建器
     *
     * @inheritdoc
     */
    public function ajaxModalListProductCondition()
    {
        $condition = $this->indexCondition();
        $condition['join'][] = [
            'table' => 'product_producer',
            'sub' => [
                'select' => [
                    'id',
                    'product_id',
                    'sort',
                    'update_time',
                    'state'
                ],
                'group' => 'product_id'
            ],
            'as' => 'producer',
            'left_on_field' => 'id',
            'right_on_field' => 'product_id'
        ];
        $condition['where'] = [
            [
                'not',
                ['producer.id' => null]
            ]
        ];

        return $condition;
    }

    /**
     * 分销产品排序辅助构建器
     *
     * @inheritdoc
     */
    public function sortProductCondition()
    {
        $condition = self::ajaxModalListProductCondition();
        unset($condition['join'][0]);
        $condition['select'] = [
            'product_upstream.name AS product_upstream_name',
            'product.id',
            'product.title'
        ];
        $condition['where'][] = ['product.state' => 1];

        return $condition;
    }

    /**
     * 产品编辑页查询构建器
     *
     * @inheritdoc
     */
    public function editCondition()
    {
        return [
            'join' => [
                [
                    'table' => 'attachment',
                    'as' => 'cover',
                    'left_on_field' => 'attachment_cover',
                ],
                ['table' => 'product_description'],
                [
                    'table' => 'product_upstream',
                    'field' => 'name'
                ]
            ],
            'select' => [
                'cover.deep_path AS cover_deep_path',
                'cover.filename AS cover_filename',
                'product_upstream.name AS product_upstream_name',
                'product_description.*',
                'product.*'
            ],
        ];
    }

    /**
     * 选择上游 - 套餐/产品分销选择时弹出层
     *
     * @auth-same product-upstream/index
     */
    public function actionAjaxModalList()
    {
        return $this->showList();
    }

    /**
     * 选择上游 - 分销产品选择时弹出层
     *
     * @auth-same product-upstream/index
     */
    public function actionAjaxModalListProduct()
    {
        return $this->showList();
    }

    /**
     * 产品数据写入前钩子
     *
     * @inheritdoc
     */
    public function preHandleField($record, $action = null)
    {
        if (!empty($record['sale_rate'])) {
            if ($record['sale_type'] == 2 && ($record['sale_rate'] < 1 || $record['sale_rate'] > 99)) {
                $this->goReference($this->getControllerName($action), [
                    'warning' => '百分比折扣时折扣率请填写 1 ~ 99 之间的数',
                    'list' => $record
                ]);
            }
        } else {
            $record['sale_rate'] = 0;
        }

        if (in_array($action, [
                'add',
                'edit'
            ]) && empty($record['package_ids']) && empty($record['new_package_ids'])
        ) {
            $this->goReference($this->getControllerName($action), [
                'warning' => '产品至少设定一个套餐',
                'list' => $record
            ]);
        }

        // TODO
        // 防止附件数据混乱, 匹配 img 标签中的 attachment-id="\d+" 属性

        return parent::preHandleField($record, $action);
    }

    /**
     * 产品数据展示前钩子
     *
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        // 生成封面图附件地址
        $record = $this->createAttachmentUrl($record, ['attachment_cover' => 'cover']);

        if ($action == 'index') {
            $record = $this->createLinkUrl($record, 'id', function ($id) {
                return [
                    'detail/index',
                    'id' => $id
                ];
            });
        }

        if (in_array($action, [
            'edit',
            'detail'
        ])) {
            // 生成其他图附件地址
            $record = $this->createAttachmentUrls($record, ['attachment_ids' => 'slave']);
            // 获取套餐数据
            $record = $this->listForeignData($record, 'package', function ($item) {
                return Helper::pullSome($item, [
                    'bidding',
                    'sale_type',
                    'sale_rate',
                    'sale_from',
                    'sale_to'
                ]);
            });
        }

        // 生成产品分销数据
        if (in_array($action, [
            'ajaxModalListProduct',
            'listProduct'
        ])) {
            $record = $this->listForeignData($record, 'producer', null, $action);

            foreach ($record['producer'] as $item) {
                $key = self::$type[$item['type']];

                if ($key == 'fixed') {
                    $commission = Helper::money($item['commission']);
                } else {
                    $commission = Helper::money($item['commission'], '%s%%');
                }

                if (empty($item['to_sales'])) {
                    $to = '+∞';
                    $tpl = '[ %s )';
                } else {
                    $to = $item['to_sales'];
                    $tpl = '[ %s ]';
                }

                $record['commission_data_' . $key][] = $item;
                $record['commission_table_' . $key][] = [
                    sprintf($tpl, "${item['from_sales']}, {$to}"),
                    $commission
                ];
            }

            unset($record['producer']);
            foreach (self::$type as $value) {
                if (!empty($record['commission_table_' . $value])) {
                    $table = ViewHelper::createTable($record['commission_table_' . $value]);
                    $record['commission_table_' . $value] = $table;
                }
            }
        }

        // TODO
        // 防止附件数据混乱, 匹配 img 标签中的 attachment-id="\d+" 属性

        return parent::sufHandleField($record, $action);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->sourceJs = [
            'jquery.ajaxupload',
            'ckeditor/ckeditor',
            '/node_modules/sortablejs/Sortable.min',
            '/node_modules/cropper/dist/cropper.min',
            '/node_modules/clipboard/dist/clipboard.min'
        ];
        $this->sourceCss = ['/node_modules/cropper/dist/cropper.min'];

        return parent::beforeAction($action);
    }
}