<?php

namespace frontend\controllers;

use common\components\Fn;
use Oil\src\Helper;
use Yii;

/**
 * Distribution controller
 */
class DistributionController extends GeneralController
{
    /**
     * Displays index.
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
        $this->sourceJs = null;

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

        $days = $this->getSignAndPrizeData();

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
            'animate',
            'day',
        ];

        return $this->render('items', compact(...$params));
    }

    /**
     * 分销商活动页 - 引导页
     *
     * @access public
     *
     * @param string $channel
     *
     * @return string
     */
    public function actionActivityBoot($channel)
    {
        $this->sourceCss = ['distribution/activity'];
        $this->sourceJs = ['distribution/activity'];

        $prize = $this->getActivityPrize();
        if (empty($prize)) {
            $this->error('今日暂无活动');
        }

        $this->seo(['title' => '分销商活动详情']);

        return $this->render('activity-boot', compact('channel', 'prize'));
    }

    /**
     * 分销商活动页 - 进行中
     *
     * @access public
     *
     * @param string $channel
     * @param string $date
     * @param integer $from
     *
     * @return string
     */
    public function actionActivity($channel, $date = null, $from = null)
    {
        $this->sourceCss = ['distribution/activity'];
        $this->sourceJs = ['distribution/activity'];

        $date = $date ?: date('Y-m-d');
        if (!strtotime($date)) {
            $this->error('日期参数不合法');
        }

        $date = date('Y-m-d', strtotime($date));

        if (strtotime($date) - strtotime(date('Y-m-d')) > 0) {
            $this->error($date . ' 的活动还未开始，请改日再来');
        }

        $prize = $this->getActivityPrize($date);
        if (empty($prize)) {
            $this->error($date . ' 无相关活动');
        }

        $code = $this->getCode($prize['prize_id']);
        $channelInfo = $this->getProducerByChannel($channel)[0];

        $this->seo([
            'title' => '我的抽奖码',
            'share_title' => '我要带你去开房~',
            'share_description' => $channelInfo['name'] . '邀你领取今日福利，活动天天有，惊喜无上限~',
            'share_cover' => current($channelInfo['logo_preview_url']),
            'share_url' => $this->currentUrl() . '&from=' . $this->user->id
        ]);

        return $this->render('activity', compact('channel', 'prize', 'code', 'channelInfo', 'from'));
    }

    /**
     * 输入手机号码获取抽奖码
     */
    public function actionAjaxCode()
    {
        $phone = Yii::$app->request->post('phone');
        $captcha = Yii::$app->request->post('captcha');
        $channel = Yii::$app->request->post('channel');

        if (empty($phone) || empty($captcha)) {
            $this->fail('手机号码或验证码参数缺失');
        }

        $prize = $this->getActivityPrize();
        if (empty($prize)) {
            $this->fail('非法操作，无可参加的活动');
        }

        $result = $this->service('activity.add-producer-code', [
            'phone' => $phone,
            'captcha' => $captcha,
            'prize' => $prize['prize_id'],
            'user' => $this->user->id,
            'from_user' => Yii::$app->request->post('from'),
            'channel' => $channel ? Helper::integerDecode($channel) : null,
        ]);

        if (is_string($result)) {
            $this->fail(Yii::t('common', $result));
        }

        $this->success();
    }

    /**
     * 获取签到过的数据及后续签到奖励
     *
     * @return array
     */
    private function getSignAndPrizeData()
    {
        return $this->cache('get-signed-and-prize', function () {

            // 已签到数据
            $signed = $this->service(parent::$apiList, [
                'table' => 'activity_producer_sign',
                'where' => [
                    ['user_id' => $this->user->id],
                    [
                        'between',
                        'add_time',
                        date('Y-m-01 00:00:00'),
                        date('Y-m-d H:i:s')
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

            // 未签到奖励
            $prizes = $this->service(parent::$apiList, [
                'table' => 'activity_producer_prize',
                'where' => [
                    [
                        '>=',
                        'from',
                        date('Y-m-d')
                    ],
                    [
                        '<=',
                        'to',
                        date('Y-m-t')
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

            return array_merge($_signed, $_prizes, [date('Y-m-d') => 'today']);
        }, strtotime(date('Y-m-t 23:59:59')) - TIME, null, Yii::$app->params['use_cache']);
    }

    /**
     * 获取活动奖品数据
     *
     * @param string $date
     *
     * @return mixed
     */
    private function getActivityPrize($date = null)
    {
        $date = $date ?: date('Y-m-d');
        $time = strtotime($date . '+1 day') - 1 - TIME;
        $time = ($time <= 0) ? 0 : $time;

        return $this->cache([
            'get-today-producer-activity',
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
     * 获取抽奖码列表
     *
     * @param integer $prizeId
     *
     * @return array
     */
    private function getCode($prizeId)
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
