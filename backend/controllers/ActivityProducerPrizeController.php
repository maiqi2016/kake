<?php

namespace backend\controllers;

use Oil\src\Helper;

/**
 * 分销商活动奖品管理
 *
 * @auth-inherit-except front sort
 */
class ActivityProducerPrizeController extends GeneralController
{
    /**
     * @var string 模型
     */
    public static $modelName = 'ActivityProducerPrize';

    /**
     * @var string 模型描述
     */
    public static $modelInfo = '分销商活动奖品';

    /**
     * @var array Ubb/Html 互转钩子
     */
    public static $hookUbbAndHtml = ['description'];

    /**
     * 列表页全局操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增奖品',
                'value' => 'activity-producer-prize/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * 列表页单记录操作按钮
     *
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '查看产品',
                'value' => 'product/index',
                'params' => function ($record) {
                    return ['id' => $record['product_id']];
                },
                'level' => 'success',
                'icon' => 'link',
                'br' => true
            ],
            [
                'text' => '查看抽奖码',
                'value' => 'activity-producer-code/index',
                'params' => function ($record) {
                    return ['activity_producer_prize_id' => $record['id']];
                },
                'level' => 'success',
                'icon' => 'link',
                'br' => true
            ],
            [
                'text' => '开奖',
                'value' => 'lottery',
                'level' => 'warning',
                'icon' => 'education',
                'show_condition' => function ($record) {
                    $show = empty($record['win_code']);
                    $show = $show && $record['state'];
                    $show = $show && strtotime(date('Y-m-d H:i:s')) > strtotime($record['to'] . ' 23:59:59');

                    return $show;
                },
                'br' => true
            ]
        ]);
    }

    /**
     * 列表页筛选器
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
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * 列表页排序器
     *
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'id',
            'from',
            'to',
            'standard_code_number',
            'state'
        ];
    }

    /**
     * 列表页的字段辅助数据
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
            'from',
            'to',
            'product_title' => [
                'title' => '产品标题',
                'max-width' => '250px'
            ],
            'product_name' => [
                'title' => '产品名称',
                'tip'
            ],
            'standard_code_number' => [
                'code'
            ],
            'win_code' => [
                'code'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * 编辑页字段辅助数据
     *
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        return [
            'product_id' => [
                'readonly' => true,
                'same_row' => true
            ],
            'select_product' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择产品',
                'script' => '$.showPage("product.list-product", {state: 1})'
            ],
            'from' => [
                'title' => '活动开始日期',
                'type' => 'date'
            ],
            'to' => [
                'title' => '活动结束日期',
                'type' => 'date'
            ],
            'standard_code_number' => [
                'placeholder' => '不小于1的正整数'
            ],
            'description' => [
                'elem' => 'ckeditor',
                'title' => '奖品描述',
                'tip' => '必须填写',
                'pos' => 'left',
                'width' => 414,
                'height' => 400
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * 列表页查询构建器
     *
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'product'],
                [
                    'left_table' => 'product',
                    'table' => 'product_upstream'
                ]
            ],
            'select' => [
                'activity_producer_prize.*',
                'product.title AS product_title',
                'product_upstream.name AS product_name',
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->sourceJs = [
            'ckeditor/ckeditor',
            'jquery.ajaxupload'
        ];

        return parent::beforeAction($action);
    }

    /**
     * 活动开奖
     * 体育彩票"排列3"上一期开奖顺序结果拼接倒序结果
     * 组成的6位数除以 1000000
     * 再乘以抽奖码总个数
     * 最后向上取整
     * 在加上 100000 即是本次开奖中奖的抽奖码。
     * JS 计算公式
     * (function(s,total){alert(Math.ceil(parseInt(s.join('')+s.reverse().join(''))/1000000*total)+100000);})([9,5,7],1000);
     *
     * @param integer $id
     */
    public function actionLottery($id)
    {
        $prize = $this->showFormWithRecord([
            'state' => 1,
            'id' => $id
        ], null, true);

        if (empty($prize)) {
            $this->error('该期活动不存在或已关闭');
        }

        if (!empty($prize['win_code'])) {
            $this->error('该期活动已操作过开奖');
        }

        $to = $prize['to'] . ' 23:59:59';
        if (strtotime(date('Y-m-d H:i:s')) <= strtotime($to)) {
            $this->error("该期活动还未结束，须于 {$to} 后开奖");
        }

        if (($total = $this->countCode($id)) < $prize['standard_code_number']) {
            $this->error("不满足抽奖码数 {$total}/{$prize['standard_code_number']} 的开奖条件，无法开奖");
        }

        // 当前时间的前一期排列三 顺序结果拼接倒序结果组成 6 位数
        $result = json_decode(Helper::cURL('http://f.apiplus.net/df6j1-1.json'), true);
        $result = str_replace(',', null, current($result['data'])['opencode']);
        $result .= strrev($result);

        // 除以 1000000 在乘以 抽奖码总数，向上取整
        $result = ceil($result / 1000000 * $total) + 100000;

        $this->actionEditForm($this->getControllerName('index'), 'edit', [
            'id' => $id,
            'win_code' => $result
        ]);
    }

    /**
     * 统计抽奖码总数
     *
     * @param integer $prizeId
     *
     * @return int
     */
    public function countCode($prizeId)
    {
        $code = $this->service(parent::$apiList, [
            'table' => 'activity_producer_code',
            'where' => [
                ['activity_producer_prize_id' => $prizeId],
                ['activity_producer_code.state' => 1]
            ],
            'select' => ['code'],
        ]);

        return count($code);
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (empty($record['standard_code_number'])) {
            $record['standard_code_number'] = 1;
        }

        return parent::sufHandleField($record, $action, $callback);
    }
}