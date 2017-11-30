<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 分销产品管理
 *
 * @auth-inherit-except front sort
 */
class ProducerProductController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerProduct';

    // 模型描述
    public static $modelInfo = '分销产品';

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['min_price'];

    public static $uid;

    /**
     * @inheritDoc
     */
    public function pageDocument()
    {
        return array_merge(parent::pageDocument(), [
            'add-my' => [
                'title_icon' => 'plus',
                'title_info' => '新增',
                'button_info' => '新增',
                'action' => 'add-my-form'
            ],
            'edit-my' => [
                'title_icon' => 'pencil',
                'title_info' => '编辑',
                'button_info' => '编辑',
                'action' => 'edit-my-form'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增分销产品',
                'value' => 'producer-product/add',
                'params' => [
                    'producer_id' => Yii::$app->request->get('producer_id')
                ],
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function myOperations()
    {
        $operations = self::indexOperations();
        $operations[0]['value'] = 'producer-product/add-my';

        return $operations;
    }

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '二维码',
                'level' => 'success',
                'icon' => 'qrcode',
                'type' => 'script',
                'value' => '$.showQrCode',
                'params' => function ($record) {
                    return ["${record['link_url']}&channel=${record['channel']}"];
                }
            ],
            [
                'alt' => '复制分销链接',
                'level' => 'default copy',
                'icon' => 'copy',
                'type' => 'attr',
                'params' => function ($record) {
                    return ['data-clipboard-text' => "${record['link_url']}&channel=${record['channel']}"];
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
                        'producer-product.sort',
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
    public static function myOperation()
    {
        $operation = self::indexOperation();
        $operation[0]['value'] = 'edit-my';

        return $operation;
    }

    /**
     * @inheritDoc
     */
    public static function myFilter()
    {
        return [
            'producer_id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'product_id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'title' => [
                'elem' => 'input',
                'title' => '产品',
                'table' => 'product'
            ],
            'name' => [
                'elem' => 'input',
                'title' => '上游',
                'table' => 'product_upstream'
            ],
            'type' => [
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
    public static function indexFilter()
    {
        $filter = self::myFilter();
        $filter['username'] = [
            'elem' => 'input',
            'title' => '分销商',
            'table' => 'user'
        ];

        return $filter;
    }

    /**
     * @inheritDoc
     */
    public static function indexSorter()
    {
        return [
            'sort'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function myAssist()
    {
        return [
            'product_id' => 'code',
            'title' => [
                'title' => '产品',
                'max-width' => '180px'
            ],
            'name' => [
                'title' => '上游',
                'max-width' => '180px',
                'tip'
            ],
            'type' => [
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'primary'
                ],
                'tip'
            ],
            'commission' => [
                'html',
                'title' => '分佣档次'
            ],
            'min_commission' => [
                'title' => '分佣起步',
                'code',
                'tpl' => '￥%s ↑'
            ],
            'add_time' => 'tip',
            'update_time' => 'tip',
            'sort' => 'code',
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'danger',
                    1 => 'info',
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        $assist = self::myAssist();
        $assist['username'] = [
            'title' => '分销商'
        ];

        return $assist;
    }

    /**
     * @inheritDoc
     */
    public static function editMyAssist()
    {
        return [
            'product_id' => [
                'readonly' => true,
                'same_row' => true,
                'label' => 2
            ],
            'select_product' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择产品',
                'script' => '$.showPage("product.list-producer", {state: 1})'
            ],
            'type' => [
                'elem' => 'select',
                'value' => 0
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
    public static function addMyAssist()
    {
        return self::editMyAssist();
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        $assist = self::editMyAssist();
        $assist['producer_id'] = [
            'readonly' => true,
            'same_row' => true,
            'label' => 2,
            'value' => intval(Yii::$app->request->get('producer_id')) ?: null,
        ];
        $assist['select_producer'] = [
            'title' => false,
            'elem' => 'button',
            'value' => '选择分销商',
            'script' => '$.showPage("producer-setting.list", {state: 1})'
        ];

        return $assist;
    }

    /**
     * @inheritDoc
     */
    public function myCondition()
    {
        return [
            'join' => [
                ['table' => 'product'],
                [
                    'table' => 'user',
                    'left_on_field' => 'producer_id'
                ],
                [
                    'left_table' => 'product',
                    'table' => 'product_upstream'
                ],
                [
                    'table' => 'product_package',
                    'sub' => [
                        'select' => [
                            'product_id',
                            'min(price) AS min_price'
                        ],
                        'where' => [
                            ['product_package.bidding' => 1],
                            ['product_package.state' => 1],
                            [
                                '>',
                                'product_package.price',
                                Yii::$app->params['commission_min_price']
                            ]
                        ],
                        'group' => 'product_id'
                    ],
                    'left_on_field' => 'product_id',
                    'right_on_field' => 'product_id'
                ],
                [
                    'left_table' => 'product',
                    'table' => 'attachment',
                    'left_on_field' => 'attachment_cover'
                ],
            ],
            'select' => [
                'product.title',
                'product.attachment_cover',
                'product.referral_link',
                'product_upstream.name',
                'producer_product.*',
                'user.username',
                'product_package.min_price',
                'attachment.deep_path AS cover_deep_path',
                'attachment.filename AS cover_filename',
            ],
            'where' => [
                ['product.state' => 1],
                ['producer_id' => self::$uid]
            ],
            'order' => [
                'producer_product.state DESC',
                'ISNULL(producer_product.sort), producer_product.sort ASC',
                'producer_product.update_time DESC'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        $condition = $this->myCondition();
        unset($condition['where'][1]);

        return array_merge(parent::indexCondition(), $condition);
    }

    /**
     * 我的分销产品
     *
     * @auth-pass-role 1,10
     * @return object
     */
    public function actionMy()
    {
        return $this->showList();
    }

    /**
     * 新增分销产品
     *
     * @return object
     */
    public function actionAddMy()
    {
        return $this->showForm();
    }

    /**
     * @auth-same {ctrl}/add-my
     */
    public function actionAddMyForm()
    {
        $post = Yii::$app->request->post();
        $post['producer_id'] = self::$uid;

        $this->actionAddForm([
            'fail' => $this->getControllerName('add-my'),
            'success' => $this->getControllerName('my')
        ], null, $post);
    }

    /**
     * 编辑分销产品
     *
     * @return object
     */
    public function actionEditMy()
    {
        return $this->showFormWithRecord();
    }

    /**
     * 列表指定用户的可结算分销订单
     *
     * @access public
     *
     * @param integer $userId
     *
     * @return array
     */
    public function listLog($userId)
    {
        $log = $this->service(self::$apiGeneralList, [
            'table' => 'producer_log',
            'join' => [
                [
                    'table' => 'order',
                    'left_on_field' => 'id',
                    'right_on_field' => 'producer_log_id'
                ]
            ],
            'where' => [
                ['order.payment_state' => 1],
                ['order.state' => 1],
                ['producer_log.producer_id' => $userId],
                ['producer_log.state' => 1]
            ],
            'select' => [
                'producer_log.*',
                'order.id AS order_id',
            ],
            'size' => 0
        ]);

        $controller = $this->controller('producer-log');
        $log = $this->callMethod('sufHandleListBeforeField', $log, [$log], $controller);

        foreach ($log as $key => $item) {
            if (empty($item['sub_counter'])) {
                unset($log[$key]);
            }
        }

        return $log;
    }

    /**
     * @auth-same {ctrl}/edit-my
     */
    public function actionEditMyForm()
    {
        if (!empty($this->listLog(self::$uid))) {
            $this->goReference($this->getControllerName('my'), [
                'warning' => '更改产品分佣策略前需确保无可结算分销订单'
            ]);
        }

        $post = Yii::$app->request->post();
        $post['producer_id'] = self::$uid;

        $this->actionEditForm([
            'fail' => $this->getControllerName('edit-my'),
            'success' => $this->getControllerName('my')
        ], null, $post);
    }

    /**
     * @inheritDoc
     */
    public function preHandleField($record, $action = null)
    {
        if (in_array($action, [
            'add',
            'add-my',
            'edit',
            'edit-my'
        ])) {
            $controller = $this->controller('product');
            $data = $this->callMethod('sufHandleField', [], [
                ['id' => $record['product_id']],
                'ajaxModalListProducer'
            ], $controller);

            if (empty($data['commission_data_' . ProductController::$type[$record['type']]])) {
                $this->goReference($this->getControllerName($action), [
                    'warning' => '该产品没有设置该分佣类型',
                    'list' => $record
                ]);
            }
        }

        return parent::preHandleField($record, $action);
    }

    /**
     * @inheritDoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (in_array($action, [
            'index',
            'my'
        ])) {
            // 生成封面图附件地址
            $record = $this->createAttachmentUrl($record, ['attachment_cover' => 'cover']);

            $record = $this->createLinkUrl($record, 'product_id', function ($id) {
                return [
                    'detail/index',
                    'id' => $id
                ];
            });
            $controller = $this->controller('product');
            $data = $this->callMethod('sufHandleField', [], [
                ['id' => $record['product_id']],
                'ajaxModalListProducer'
            ], $controller);

            $key = $record['type'] ? 'commission_table_percent' : 'commission_table_fixed';
            $record['commission'] = isset($data[$key]) ? $data[$key] : null;

            $key = $record['type'] ? 'commission_data_percent' : 'commission_data_fixed';
            $record['commission_data'] = isset($data[$key]) ? $data[$key] : [];

            $record['channel'] = Helper::integerEncode($record['producer_id']);

            // 计算最低分佣金
            $data = current($record['commission_data']);

            $controller = $this->controller('producer-log');
            $price = $record['min_price'] / 100;
            $record['min_commission'] = $controller::calCommission($data['type'], $price, $price, $data['commission']);
        }

        return parent::sufHandleField($record, $action, $callback);
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'index',
            'my'
        ])) {
            $this->sourceJs = ['/node_modules/clipboard/dist/clipboard.min'];
        }

        parent::beforeAction($action);
        self::$uid = $this->user->id;

        return true;
    }
}
