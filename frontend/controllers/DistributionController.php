<?php

namespace frontend\controllers;

use common\components\Fn;
use Oil\src\Helper;
use Yii;
use yii\helpers\Url;

/**
 * Distribution controller
 */
class DistributionController extends GeneralController
{
    /**
     * 分销商首页 Old
     *
     * @access public
     *
     * @param string $channel
     *
     * @return string
     */
    public function actionIndex($channel)
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        list($producer, $uid) = $this->getProducerByChannel($channel);
        if (is_string($producer)) {
            $this->error($producer);
        }

        // 获取分销产品
        $product = $this->listProducerProduct($uid, null, 1, Yii::$app->params['distribution_limit']);
        if (empty($product)) {
            $this->error(Yii::t('common', 'the distributor need select product first'));
        }

        $this->seo([
            'title' => $producer['name'] . '的小店',
            'share_title' => $producer['name'] . '的小店',
            'share_cover' => current($producer['logo_preview_url'])
        ]);

        return $this->render('index', compact('producer', 'product'));
    }

    /**
     * 分销商首页
     *
     * @access public
     *
     * @param string $channel
     *
     * @return string
     */
    public function actionItems($channel)
    {
        $this->sourceCss = null;
        $this->sourceJs = [
            'distribution/items',
            '/node_modules/moment/min/moment.min'
        ];

        $params = Yii::$app->params;

        $classify = parent::model('product_upstream')->_classify;

        // 焦点图
        $focusList = $this->listAd(2, $params['distribution_ad_focus_limit']);

        // 广告
        $bannerList = $this->listAd(3, $params['distribution_ad_banner_limit']);

        $region = $this->listPlateAndRegion();
        $upstream = $this->listUpstreams(function ($item) {
            $item['name'] = preg_replace('/[ ]+\|[ ]+/', ' ', $item['name']);

            return $item;
        });

        list($producer, $uid) = $this->getProducerByChannel($channel);
        if (is_string($producer)) {
            $this->error($producer);
        }

        $list = $this->listProducerProduct($uid, 0, 1, $params['distribution_items_limit']);
        $top = $this->renderPartial('top-list', ['list' => array_slice($list, 0, 4)]);
        $html_0 = $this->renderPartial('items-list', ['list' => array_slice($list, 4)]);

        $list = $this->listProducerProduct($uid, 1, 1, $params['distribution_items_limit']);
        $html_1 = $this->renderPartial('items-list', compact('list'));

        $list = $this->listProducerProduct($uid, 2, 1, $params['distribution_items_limit']);
        $html_2 = $this->renderPartial('items-list', compact('list'));

        $ref = $this->reference();
        $animate = true;
        if (!empty($ref) && strpos($ref, Yii::$app->params['frontend_url']) !== false) {
            $animate = false;
        }

        $this->seo([
            'title' => $producer['name'],
            'share_title' => $producer['name'],
            'share_cover' => current($producer['logo_preview_url'])
        ]);

        $params = [
            'classify',
            'focusList',
            'bannerList',
            'region',
            'upstream',
            'producer',
            'top',
            'html_0',
            'html_1',
            'html_2',
            'animate'
        ];

        return $this->render('items', compact(...$params));
    }

    public function actionDigitalTv()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        return $this->render('digital-tv');
    }

    /**
     * 设置分享信息
     *
     * @param string $title
     * @param string $channel
     * @param string $date
     */
    private function share($title, $channel, $date)
    {
        $this->seo([
            'title' => $title,
            'share_title' => Yii::$app->params['activity_producer_share_title'],
            'share_description' => Yii::$app->params['activity_producer_share_description'],
            'share_cover' => Yii::$app->params['frontend_source'] . '/img/distribution/activity-boot/share.png',
            'share_url' => Url::toRoute([
                'distribution/activity-boot',
                'channel' => $channel,
                'date' => $date,
                'from_user' => $this->user->id
            ], true)
        ]);
    }

    /**
     * 分销商活动页 - 引导页
     *
     * @access public
     *
     * @param string  $channel
     * @param string  $date
     * @param integer $from_user
     *
     * @return string
     */
    public function actionActivityBoot($channel = null, $date = null, $from_user = null)
    {
        $this->sourceCss = ['distribution/activity'];
        $this->sourceJs = ['distribution/activity'];

        $date = $this->validateDate($date);
        $prize = $this->getPrizeData($date);
        if (empty($prize)) {
            $this->error($date . ' 这天暂无活动');
        }

        $hasCode = $this->service(parent::$apiDetail, [
            'table' => 'activity_producer_code',
            'where' => [
                ['activity_producer_prize_id' => $prize['prize_id']],
                ['user_id' => $this->user->id],
                ['state' => 1],
                ['from_user_id' => null]
            ],
            'select' => [
                'id',
                'phone'
            ]
        ]);

        $did = strtotime(date($prize['to'])) < strtotime(date('Y-m-d 00:00:00'));
        $will = strtotime(date($prize['from'])) > strtotime(date('Y-m-d 00:00:00'));

        if (!empty($hasCode) && !($did || $will)) {
            return $this->redirect([
                'distribution/activity',
                'channel' => $channel
            ]);
        }

        $code_list_url = (!empty($hasCode) && $did) ? Url::toRoute([
            'distribution/activity',
            'date' => $date
        ]) : null;

        $this->share('活动详情', $channel, $date);

        return $this->render('activity-boot', compact('channel', 'prize', 'from_user', 'code_list_url'));
    }

    /**
     * 分销商活动页 - 进行中
     *
     * @access public
     *
     * @param string $channel
     * @param string $date
     *
     * @return string
     */
    public function actionActivity($channel = null, $date = null)
    {
        $this->sourceCss = ['distribution/activity'];
        $this->sourceJs = ['distribution/activity'];

        $date = $this->validateDate($date);

        if (strtotime($date) - strtotime(date('Y-m-d')) > 0) {
            $this->error($date . ' 的活动还未开始，请改日再来');
        }

        $prize = $this->getPrizeData($date);
        if (empty($prize)) {
            $this->error($date . ' 这天暂无活动');
        }

        $code = $this->getMyCode($prize['prize_id']);

        $controller = $this->controller('activity-producer-prize');
        $total = $this->callMethod('countCode', 0, [$prize['prize_id']], $controller);

        $percent = floor($total / $prize['standard_code_number'] * 100);
        // $percent = $percent > 100 ? 100 : $percent;

        // history winner
        $history_winner = $this->listHistoryWinner();

        $user = $this->user;
        $this->share('我的抽奖码', $channel, $date);

        return $this->render('activity', compact('channel', 'prize', 'code', 'total', 'percent', 'user', 'history_winner'));
    }

    /**
     * Ajax 参与活动获取抽奖码
     */
    public function actionAjaxCode()
    {
        $phone = Yii::$app->request->post('phone');
        $captcha = Yii::$app->request->post('captcha');
        $channel = Yii::$app->request->post('channel');

        if (empty($phone) || empty($captcha)) {
            $this->fail('手机号码或验证码参数缺失');
        }

        $prize = $this->getPrizeData();
        if (empty($prize)) {
            $this->fail('非法操作，无可参加的活动');
        }

        $result = $this->service('activity.add-producer-code', [
            'phone' => $phone,
            'captcha' => $captcha,
            'prize' => $prize['prize_id'],
            'user' => $this->user->id,
            'from_user' => Yii::$app->request->post('from_user'),
            'channel' => $channel ? Helper::integerDecode($channel) : null,
        ]);

        if (is_string($result)) {
            $this->fail(Yii::t('common', $result));
        }

        $this->success([
            'href' => Url::toRoute([
                'distribution/activity',
                'channel' => $channel
            ])
        ]);
    }

    /**
     * Ajax 获取签到和奖励数据
     */
    public function actionAjaxDays()
    {
        $date = $this->validateDate(Yii::$app->request->post('date'));
        $days = array_merge($this->getPrize($date), $this->getSigned($date));

        $this->success($days);
    }

    # --- Functions ---

    /**
     * 获取合法日期
     *
     * @param string $date
     *
     * @return string
     */
    private function validateDate($date)
    {
        $date = $date ?: date('Y-m-d');
        if (!($time = strtotime($date))) {
            $message = '日期参数不合法';
            Yii::$app->request->isAjax ? $this->fail($message) : $this->error($message);
        }

        return date('Y-m-d', $time);
    }

    /**
     * 比较两个日期变量的大小
     *
     * @param mixed $future
     * @param mixed $ago
     *
     * @return boolean
     */
    private function later($future, $ago)
    {
        $future = !is_numeric($future) ? strtotime($future) : $future;
        $ago = !is_numeric($ago) ? strtotime($ago) : $ago;

        return $future > $ago;
    }

    /**
     * 获取签到过的数据
     *
     * @param string $date
     *
     * @return array
     */
    private function getSigned($date = null)
    {
        $date = $this->validateDate($date);
        if ($this->later($date, date('Y-m-t 23:59:59'))) {
            return [];
        }

        $key = [
            'get.signed',
            func_get_args(),
            $this->user->id
        ];

        $cacheTime = $this->later(date('Y-m-1 00:00:00'), $date) ? DAY : MINUTE;

        return $this->cache($key, function () use ($date) {

            list($year, $month) = explode('-', $date);

            $signed = $this->service(parent::$apiList, [
                'table' => 'activity_producer_sign',
                'where' => [
                    ['user_id' => $this->user->id],
                    [
                        'between',
                        'add_time',
                        date("{$year}-{$month}-1 00:00:00"),
                        date("{$year}-{$month}-t 23:59:59")
                    ],
                    ['state' => 1]
                ],
                'select' => 'add_time'
            ]);

            $signed = array_column($signed, 'add_time');
            $_signed = [];
            foreach ($signed as $item) {
                $_signed[explode(' ', $item)[0]] = 'signed';
            }

            return $_signed;
        }, $cacheTime, null, Yii::$app->params['use_cache']);
    }

    /**
     * 获取某天所在月的奖品数据
     *
     * @param string $date
     *
     * @return array
     */
    private function getPrize($date = null)
    {
        $date = $this->validateDate($date);

        $key = [
            'get.prize',
            func_get_args()
        ];

        return $this->cache($key, function () use ($date) {

            list($year, $month) = explode('-', $date);

            $prizes = $this->service(parent::$apiList, [
                'table' => 'activity_producer_prize',
                'where' => [
                    [
                        '>=',
                        'to',
                        date("{$year}-{$month}-1")
                    ],
                    [
                        '<=',
                        'to',
                        date("{$year}-{$month}-t")
                    ],
                    ['activity_producer_prize.state' => 1]
                ],
                'join' => [
                    ['table' => 'product'],
                    [
                        'left_table' => 'product',
                        'table' => 'product_upstream'
                    ]
                ],
                'select' => [
                    'from',
                    'to',
                    'product_upstream.classify'
                ],
            ]);

            $_prizes = [];
            foreach ($prizes as $item) {
                if ($item['from'] === $item['to']) {
                    $_prizes[$item['from']] = $item['classify'];
                } else {
                    $from = strtotime($item['from']);
                    $lastDay = strtotime($item['to']);
                    while ($from <= $lastDay) {
                        $_prizes[date('Y-m-d', $from)] = $item['classify'];
                        $from += 86400;
                    }
                }
            }

            return $_prizes;
        });
    }

    /**
     * 获取活动奖品数据
     *
     * @param string $date
     *
     * @return mixed
     */
    private function getPrizeData($date = null)
    {
        $date = $date ?: date('Y-m-d');
        $time = strtotime($date . '+1 day') - 1 - TIME;
        $time = ($time <= 0) ? 0 : $time;

        return $this->cache([
            'get.today.producer.activity',
            func_get_args()
        ], function () use ($date) {
            $prize = $this->service(parent::$apiDetail, [
                'table' => 'activity_producer_prize',
                'where' => [
                    [
                        '<=',
                        'from',
                        $date
                    ],
                    [
                        '>=',
                        'to',
                        $date
                    ],
                    ['activity_producer_prize.state' => 1]
                ],
                'join' => [
                    ['table' => 'product'],
                    [
                        'left_table' => 'product',
                        'table' => 'product_package',
                        'sub' => [
                            'select' => [
                                'product_id',
                                'min(price) AS price'
                            ],
                            'where' => [
                                ['product_package.bidding' => 1],
                                ['product_package.state' => 1]
                            ],
                            'group' => 'product_id'
                        ],
                        'left_on_field' => 'id',
                        'right_on_field' => 'product_id'
                    ],
                    [
                        'left_table' => 'product',
                        'table' => 'attachment',
                        'as' => 'cover',
                        'left_on_field' => 'attachment_cover',
                        'right_on_field' => 'id'
                    ],
                    [
                        'left_table' => 'product',
                        'table' => 'product_upstream',
                        'left_on_field' => 'product_upstream_id',
                        'right_on_field' => 'id'
                    ]
                ],
                'select' => [
                    'activity_producer_prize.id AS prize_id',
                    'activity_producer_prize.description',
                    'activity_producer_prize.from',
                    'activity_producer_prize.to',
                    'activity_producer_prize.standard_code_number',
                    'activity_producer_prize.win_code',
                    'product.id',
                    'product.title',
                    'product.attachment_cover',
                    'product_package.price AS sale_price',
                    'cover.deep_path AS cover_deep_path',
                    'cover.filename AS cover_filename',
                    'product_upstream.name',
                ]
            ]);

            $prize = $this->callMethod('sufHandleField', $prize, [$prize], $this->controller('activity-producer-prize'));
            $prize = $this->callMethod('sufHandleField', $prize, [
                $prize,
                'index'
            ], $this->controller('product'));

            return $prize;
        }, $time, null, Yii::$app->params['use_cache']);
    }

    /**
     * 获取活动历史中奖用户
     *
     * @return mixed
     */
    private function listHistoryWinner()
    {
        $date = date('Y-m-d');
        $time = strtotime($date . '+1 day') - 1 - TIME;

        return $this->cache('list.history.winner', function () use ($date) {
            $prize = $this->service(parent::$apiList, [
                'table' => 'activity_producer_prize',
                'where' => [
                    [
                        '<=',
                        'to',
                        $date
                    ],
                    [
                        'not',
                        ['win_code' => null]
                    ],
                    ['activity_producer_prize.state' => 1],
                ],
                'join' => [
                    [
                        'table' => 'activity_producer_code',
                        'left_on_field' => 'win_code',
                        'right_on_field' => 'code',
                        'as' => 'code'
                    ],
                    [
                        'left_table' => 'code',
                        'table' => 'user'
                    ]
                ],
                'select' => [
                    'activity_producer_prize.from',
                    'activity_producer_prize.to',
                    'activity_producer_prize.win_code',
                    'code.phone',
                    'user.username',
                    'user.head_img_url'
                ],
                'order' => 'activity_producer_prize.to DESC'
            ]);

            array_walk($prize, function (&$item) {
                $item = $this->callMethod('sufHandleField', $item, [$item], $this->controller('activity-producer-prize'));
            }, $prize);

            return $prize;
        }, $time, null, Yii::$app->params['use_cache']);
    }

    /**
     * 获取我的抽奖码列表
     *
     * @param integer $prizeId
     *
     * @return array
     */
    private function getMyCode($prizeId)
    {
        $code = $this->service(parent::$apiList, [
            'table' => 'activity_producer_code',
            'where' => [
                ['activity_producer_prize_id' => $prizeId],
                ['user_id' => $this->user->id],
                ['activity_producer_code.state' => 1]
            ],
            'join' => [
                [
                    'table' => 'user',
                    'left_on_field' => 'from_user_id',
                ]
            ],
            'select' => [
                'code',
                'user.username AS from_user'
            ],
        ]);

        return array_column($code, 'from_user', 'code');
    }
}
