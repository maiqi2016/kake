<?php

namespace backend\controllers;

use backend\components\ViewHelper;
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

    // 当前用户ID
    public static $uid;

    // 统一排序列表
    public static $unifySortList = null;

    /**
     * @var array Hook
     */
    public static $hookPriceNumber = ['min_price'];

    /**
     * @inheritDoc
     */
    public function unifySortInitScript()
    {
        return ['$.sortable("#unify-sort", "input[name=unify_sort]", 0)'];
    }

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
            ],
            'unify-sort' => [
                'title_icon' => 'sort-by-attributes',
                'title_info' => '分销商统一排序',
                'button_info' => '清空原排序并提交',
                'button_level' => 'warning',
                'action' => 'update-unify-sort',
                'info_perfect' => true
            ],
            'clone-sort' => [
                'title_icon' => 'copy',
                'title_info' => '分销商克隆排序',
                'button_info' => '清空原排序并克隆',
                'button_level' => 'warning',
                'action' => 'update-clone-sort',
                'info_perfect' => true
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
            ],
            [
                'text' => '统一排序',
                'value' => 'producer-product/unify-sort',
                'icon' => 'sort-by-attributes'
            ],
            [
                'text' => '克隆排序',
                'value' => 'producer-product/clone-sort',
                'icon' => 'copy'
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
                'script' => '$.showPage("product.list-product", {state: 1})'
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
     * 创建统一排序 html
     *
     * @access private
     * @return string
     */
    private static function createSortHtml()
    {
        $colorArr = [
            'success',
            'info',
            'warning',
            'danger'
        ];
        $max = count($colorArr);

        $html = '<div id="unify-sort">';
        foreach (self::$unifySortList as $key => $item) {
            $id = str_pad($item['id'], 3, 0, STR_PAD_LEFT);
            $upstream = "title='{$item['product_upstream_name']}'";
            $color = $colorArr[$key % $max];
            $html .= "<p class='bg-{$color} sortable-box' id='{$item['id']}' {$upstream}><kbd>{$id}</kbd> {$item['title']}</p>";
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @inheritDoc
     */
    public static function unifySortAssist()
    {
        return [
            'unify_sort' => [
                'hidden' => true,
                'value' => implode(',', array_column(self::$unifySortList, 'id'))
            ],
            'tip' => [
                'title' => '',
                'elem' => 'text',
                'label' => 6,
                'html' => true,
                'value' => "<h3>拖拽产品列表进行排序</h3><i>提交后将按此顺序改变每一个分销商分销产品的排序，本列表顺序也将更新</i>"
            ],
            'list' => [
                'title' => '产品列表',
                'elem' => 'text',
                'html' => true,
                'label' => 7,
                'value' => self::createSortHtml()
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function cloneSortAssist()
    {
        $elem = [
            'elem' => 'select',
            'list_table' => 'producer_setting',
            'list_value' => 'name',
            'list_key' => 'producer_id',
            'list_where' => [
                ['state' => 1]
            ],
            'label' => 3
        ];

        return [
            'from' => array_merge($elem, ['title' => '克隆源']),
            'arrow' => [
                'title' => '',
                'elem' => 'text',
                'value' => str_repeat(SPACE, 7) . '↓ ↓ ↓ ↓ ↓'
            ],
            'to' => array_merge($elem, ['title' => '克隆给'])
        ];
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
        array_unshift($condition['order'], 'producer_product.producer_id ASC');

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
     * 分销产品排序
     *
     * @return object
     */
    public function actionUnifySort()
    {
        $controller = $this->controller('product');
        list(self::$unifySortList) = $controller->showList('sortProduct', true, false, [
            'size' => 0,
            'order' => [
                'producer.state DESC',
                'ISNULL(producer.sort), producer.sort ASC',
                'producer.update_time DESC',
            ]
        ], false);

        return $this->showForm();
    }

    /**
     * @auth-same {ctrl}/unify-sort
     */
    public function actionUpdateUnifySort()
    {
        $this->service('producer.unify-sort', [
            'sort' => Yii::$app->request->post('unify_sort')
        ]);

        $this->goReference($this->getControllerName('index'), [
            'success' => '统一排序完成'
        ]);
    }

    /**
     * 克隆排序
     *
     * @return object
     */
    public function actionCloneSort()
    {
        return $this->showForm();
    }

    /**
     * @auth-same {ctrl}/clone-sort
     */
    public function actionUpdateCloneSort()
    {
        $params = Yii::$app->request->post();
        if ($params['from'] == $params['to']) {
            $this->goReference($this->getControllerName('clone-sort'), [
                'warning' => '克隆源和目标请选择不同的分销商',
                'list' => $params
            ]);
        }

        $this->service('producer.clone-sort', Helper::pullSome($params, [
            'from',
            'to'
        ]));

        $this->goReference($this->getControllerName('clone-sort'), [
            'success' => '克隆排序完成',
            'list' => $params
        ]);
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
                'ajaxModalListProduct'
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
                'ajaxModalListProduct'
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
        } else if ($action->id == 'unify-sort') {
            $this->sourceJs = ['/node_modules/sortablejs/Sortable.min'];
        }

        parent::beforeAction($action);
        self::$uid = $this->user->id;

        return true;
    }
}
