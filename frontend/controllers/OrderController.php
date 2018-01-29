<?php

namespace frontend\controllers;

use Oil\src\Helper;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Order controller
 */
class OrderController extends GeneralController
{
    /**
     * @const pay code for channel
     */
    const PAY_CODE_WX = 0;
    const PAY_CODE_ALI = 1;

    /**
     * @var array 支付方式
     */
    public static $paymentMethod = [
        0 => 'wx',
        1 => 'ali'
    ];

    /**
     * @var array 子订单查询条件
     */
    public static $orderSubCondition = [
        'table' => 'order_sub',
        'join' => [
            ['table' => 'order'],
            ['table' => 'product_package'],
            [
                'left_table' => 'order',
                'table' => 'product'
            ],
            [
                'left_table' => 'product',
                'table' => 'attachment',
                'left_on_field' => 'attachment_cover',
            ],
            [
                'left_table' => 'product',
                'table' => 'product_upstream'
            ],
            [
                'table' => 'order_bill',
                'left_on_field' => 'id',
                'right_on_field' => 'order_sub_id'
            ],
            [
                'table' => 'order_instructions_log',
                'sub' => [
                    'from' => [
                        'sub' => [
                            'select' => [
                                'order_sub_id',
                                'remark',
                                'add_time'
                            ],
                            'order' => ['add_time DESC'],
                            'limit' => 10000
                        ],
                    ],
                    'select' => [
                        'order_sub_id',
                        'remark'
                    ],
                    'order' => ['add_time DESC'],
                    'group' => 'order_sub_id'
                ],
                'as' => 'log',
                'left_on_field' => 'id',
                'right_on_field' => 'order_sub_id'
            ],
            [
                'table' => 'order_sold_code',
                'left_on_field' => 'id',
                'right_on_field' => 'order_sub_id'
            ]
        ],
        'select' => [
            'order_sub.*',

            'order.order_number',
            'order.payment_method',
            'order.payment_state',

            'product_package.name AS package_name',
            'product_package.bidding',
            'product_package.supplier_contact',

            'product.title',
            'product.attachment_cover',

            'attachment.deep_path AS cover_deep_path',
            'attachment.filename AS cover_filename',

            'product_upstream.name AS product_upstream_name',

            'order_bill.id AS order_bill_id',
            'order_bill.courier_company',
            'order_bill.courier_number',
            'order_bill.invoice_title',
            'order_bill.address',

            'log.remark',

            'order_sold_code.code',
        ],
        'where' => [
            [
                '<>',
                'order.state',
                0
            ]
        ],
        'order' => [
            'order_sub.add_time DESC',
            'order_sub.id DESC'
        ],
        'distinct' => true,
    ];

    /**
     * @var array Order list map
     */
    public $orderListMap = [
        'ongoing' => [
            0,
            1,
            2,
            3
        ],
        'completed' => [
            4,
            5,
            6
        ]
    ];

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'wx-paid',
            'ali-paid'
        ])) {
            $action->controller->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * 订单中心
     *
     * @param string $type
     *
     * @return string
     */
    public function actionIndex($type = 'ongoing')
    {
        if (!isset($this->orderListMap[$type])) {
            $this->error(Yii::t('common', 'order list type error'));
        }

        $this->sourceCss = null;
        $this->sourceJs = [
            'order/index'
        ];

        list($html, $over) = $this->renderListPage(1, $type);
        $this->seo(['title' => '订单中心']);

        return $this->render('index-' . $type, compact('html', 'over'));
    }

    /**
     * ajax 获取下一页订单列表
     */
    public function actionAjaxList()
    {
        $page = Yii::$app->request->post('page');
        $type = Yii::$app->request->post('type');

        list($html, $over) = $this->renderListPage($page, $type);
        $this->success(compact('html', 'over'));
    }

    /**
     * 渲染订单列表 html
     *
     * @access private
     *
     * @param integer $page
     * @param string  $type
     *
     * @return array
     */
    private function renderListPage($page, $type)
    {
        if (!isset($this->orderListMap[$type])) {
            return [
                null,
                true
            ];
        }

        $pageSize = Yii::$app->params['order_page_size'];
        $list = $this->listOrderSub($page, $this->orderListMap[$type], $pageSize);
        $content = $this->renderPartial('list-' . $type, compact('list'));

        return [
            $content,
            count($list) < $pageSize
        ];
    }

    /**
     * 展示核销产品的二维码
     */
    public function actionAjaxSoldQrCode()
    {
        $orderSubId = Yii::$app->request->post('order_sub_id');

        $code = $this->service(parent::$apiDetail, [
            'table' => 'order_sold_code',
            'join' => [
                ['table' => 'order_sub'],
                [
                    'left_table' => 'order_sub',
                    'table' => 'order'
                ]
            ],
            'where' => [
                ['order_sold_code.state' => 1],
                ['order_sold_code.order_sub_id' => $orderSubId],
                ['order.user_id' => $this->user->id]
            ]
        ]);

        $url = Url::toRoute([
            'order/verify-sold',
            'sold' => $code['code']
        ], true);
        $qr = $this->createQrCode($url, 200);

        $this->success(['message' => Html::img($qr->writeDataUri())]);
    }

    /**
     * 核销
     *
     * @access public
     *
     * @param string $sold
     *
     * @return string
     */
    public function actionVerifySold($sold = null)
    {
        if (!isset($sold)) {
            $this->sourceJs = ['order/index'];
            $this->sourceCss = ['order/verify-sold'];

            $this->seo([
                'title' => 'KAKE核销'
            ]);

            return $this->render('verify-sold');
        }

        if (!in_array($this->user->role, [
            1,
            9
        ])
        ) {
            $this->error(Yii::t('common', 'is not supplier'));
        }

        $sold = str_replace(' ', null, $sold);
        if (empty($sold)) {
            $this->error(Yii::t('common', 'sold code required'));
        }

        $supplier = $this->listSupplier($this->user->id);
        $result = $this->service('order.verify-sold-code', [
            'sold' => $sold,
            'supplier' => $supplier
        ]);

        if (is_string($result)) {
            $this->error(Yii::t('common', $result));
        }

        return $this->redirect(['order/verify-sold-result']);
    }

    /**
     * 核销结果页
     *
     * @access public
     * @return string
     */
    public function actionVerifySoldResult()
    {
        $this->sourceCss = ['order/verify-sold'];

        return $this->render('verify-sold-result');
    }

    /**
     * 立即支付
     */
    public function actionAjaxPaymentAgain()
    {
        $paymentMethod = Yii::$app->request->post('payment_method');
        $orderNumber = Yii::$app->request->post('order_number');

        if (!isset(self::$paymentMethod[$paymentMethod])) {
            $this->error(Yii::t('common', 'param illegal', ['param' => 'payment_method']));
        }
        $paymentMethod = self::$paymentMethod[$paymentMethod];

        $this->success($this->createSafeLink([
            'order_number' => $orderNumber
        ], 'order/' . $paymentMethod . '-pay/', $paymentMethod == 'ali' ? false : true));
    }

    /**
     * 取消订单
     */
    public function actionAjaxCancelOrder()
    {
        $orderNumber = Yii::$app->request->post('order_number');

        $result = $this->service('order.cancel-order', [
            'user_id' => $this->user->id,
            'order_number' => $orderNumber
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'cancel order success');
    }

    /**
     * 退款申请
     */
    public function actionAjaxApplyRefund()
    {
        $result = $this->service('order.apply-refund', [
            'user_id' => $this->user->id,
            'order_sub_id' => Yii::$app->request->post('id'),
            'remark' => Yii::$app->request->post('remark')
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'refund request submitted');
    }

    /**
     * 预约申请
     */
    public function actionAjaxApplyOrder()
    {
        $result = $this->service('order.apply-order', [
            'user_id' => $this->user->id,
            'order_sub_id' => Yii::$app->request->post('id'),
            'name' => Yii::$app->request->post('name'),
            'phone' => Yii::$app->request->post('phone'),
            'time' => Yii::$app->request->post('time')
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'order request submitted');
    }

    /**
     * 我已入住
     */
    public function actionAjaxCompleted()
    {
        $result = $this->service('order.completed', [
            'user_id' => $this->user->id,
            'order_sub_id' => Yii::$app->request->post('id')
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'check in success');
    }

    /**
     * 开具发票
     */
    public function actionAjaxApplyBill()
    {
        $params = Yii::$app->request->post();

        $result = $this->service('order.apply-bill', [
            'user_id' => $this->user->id,
            'order_sub_id' => $params['id'],
            'invoice_title' => $params['company'] ? $params['company_name'] : null,
            'tax_number' => $params['company'] ? $params['tax_number'] : null,
            'address' => $params['address']
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'invoice request submitted');
    }

    /**
     * 获取主订单详情
     *
     * @access public
     *
     * @param string $param
     * @param string $field
     *
     * @return array
     */
    public function getOrder($param, $field = 'id')
    {
        if (empty($param)) {
            $this->error(Yii::t('common', 'order param required'));
        }

        $detail = $this->service('order.detail', [
            'join' => [
                ['table' => 'product'],
            ],
            'select' => [
                'product.*',
                'order.*'
            ],
            'order' => ['order.id DESC'],
            'where' => [
                ['order.' . $field => $param],
                ['order.state' => 1]
            ]
        ]);

        return $detail;
    }

    /**
     * 第三方下单前的本地下单
     *
     * @access private
     *
     * @param integer $payCode
     * @param boolean $checkUser
     *
     * @return array
     */
    private function localOrder($payCode, $checkUser = true)
    {
        $params = $this->validateSafeLink($checkUser);

        $product = $this->getProduct($params['product_id']);
        if (empty($product)) {
            $this->error(Yii::t('common', 'product does not exist'));
        }

        $packageData = $this->listProductPackage($params['product_id']);
        if (empty($packageData)) {
            $this->error(Yii::t('common', 'product package does not exist'));
        }

        $packagePurchaseTimes = $this->service('order.purchase-times', [
            'user_id' => $this->user->id
        ], 'yes');

        $price = 0;
        $_package = [];
        foreach ((array) $params['package'] as $id => $number) {
            if (!isset($packageData[$id])) {
                $this->error(Yii::t('common', 'product package illegal'));
            }

            $limit = 'purchase_limit';
            if (!empty($packageData[$id][$limit])) {
                if (empty($packagePurchaseTimes[$id])) {
                    if ($number > $packageData[$id][$limit]) {
                        $this->error(Yii::t('common', 'product package greater then limit', [
                            'buy' => $number,
                            'max' => $packageData[$id][$limit]
                        ]));
                    }
                } else {
                    if ($number > $packageData[$id][$limit] - $packagePurchaseTimes[$id]) {
                        $this->error(Yii::t('common', 'product package greater then limit with purchased', [
                            'buy' => $number,
                            'max' => $packageData[$id][$limit],
                            'buys' => $packagePurchaseTimes[$id]
                        ]));
                    }
                }
            }

            $_package[$id] = $packageData[$id];
            $_package[$id]['number'] = $number;
            $_package[$id]['price'] = intval($packageData[$id]['min_price'] * 100);

            $price += $_package[$id]['price'] * $number;
        }

        // 生成订单编号
        $orderNumber = Helper::createOrderNumber($payCode, $this->user->id);

        // 本地下单
        $channel = Yii::$app->request->get('channel');
        $result = $this->service('order.add', [
            'order_number' => $orderNumber,
            'user_id' => $this->user->id,
            'product_id' => $product['id'],
            'payment_method' => $payCode,
            'price' => $price,
            'order_contacts_id' => $params['order_contacts_id'],
            'package' => $_package,
            'producer_id' => Helper::integerDecode($channel)
        ]);

        if (is_string($result)) {
            $this->error(Yii::t('common', $result));
        }

        $ids = array_merge($this->getRootUsers(), Helper::handleString(Yii::$app->params['order_notice_user_ids']));
        $openidArr = $this->listUser([
            ['manager' => 1],
            ['role' => 1]
        ], 'openid', $ids);
        foreach ($openidArr as $uid => $openid) {
            if (empty($openid)) {
                continue;
            }



            Yii::$app->oil->wx->sendTplMsg([
                'to' => $openid,
                'tpl' => 'wUH-x5gnE6O8n9O8wAaFcHVDWhpf7DctTRqQDS-8BeA',
                'header' => '平台有新的订单产生',
                'footer' => '订单管理与追踪请登录后台系统'
            ], [
                date('Y-m-d H:i:s'),
                (empty($channel) ? '平台流量' : '分销渠道') . ' (UID: ' . $this->user->id . ')',
                $orderNumber,
                Helper::money($price / 100),
            ]);
        }

        return [
            $orderNumber,
            $product['title'],
            $price
        ];
    }

    // --↓↓- WeChat Payment -↓↓--

    /**
     * 微信下单
     *
     * @access  public
     * @link    http://leon.m.kakehotels.com/order/wx/?xxx
     * @license link create by $this->createSafeLink()
     * @return string
     */
    public function actionWx()
    {
        if (!Helper::weChatBrowser()) {
            $this->error(Yii::t('common', 'payment with wechat must on the client'));
        }

        list($outTradeNo, $body, $price) = $this->localOrder(self::PAY_CODE_WX);

        return $this->wxPay($outTradeNo, $body, $price);
    }

    /**
     * 微信支付订单（可重复调用）
     *
     * @link http://leon.m.kakehotels.com/order/wx-pay/?xxx
     * @return string
     */
    public function actionWxPay()
    {
        if (!Helper::weChatBrowser()) {
            $this->error(Yii::t('common', 'payment with wechat must on the client'));
        }

        $params = $this->validateSafeLink();
        $order = $this->getOrder($params['order_number'], 'order_number');

        // 查询订单
        $result = Yii::$app->oil->wx->payment->query($order['order_number']);

        $stateInfo = [
            'SUCCESS' => '支付成功',
            'REFUND' => '转入退款',
            'NOTPAY' => '未支付',
            'CLOSED' => '已关闭',
            'REVOKED' => '已撤销(刷卡支付)',
            'USERPAYING' => '用户支付中',
            'PAYERROR' => '支付失败(如银行返回失败)',
        ];

        if (!in_array($result->trade_state, [
            'NOTPAY',
            'PAYERROR'
        ])
        ) {
            $this->error(Yii::t('common', 'resubmit the order please'));
        }

        // 生成订单编号
        $orderNumber = Helper::createOrderNumber(self::PAY_CODE_WX, $this->user->id);

        // 更新本地订单编号
        $result = $this->service('order.update-order-number', [
            'id' => $order['id'],
            'order_number' => $orderNumber
        ]);

        if (is_string($result)) {
            $this->error(Yii::t('common', $result));
        }

        // 关闭旧订单
        Yii::$app->oil->wx->payment->close($order['order_number']);

        return $this->wxPay($orderNumber, $order['title'], $order['price']);
    }

    /**
     * 微信支付页面渲染
     *
     * @param string $outTradeNo
     * @param string $body
     * @param float  $price
     *
     * @return string
     */
    private function wxPay($outTradeNo, $body, $price)
    {
        try {
            $prepayId = Yii::$app->oil->wx->order([
                'body' => $body,
                'out_trade_no' => $outTradeNo,
                'total_fee' => $price,
                'notify_url' => SCHEME . Yii::$app->params['frontend_url'] . '/order/wx-paid/',
                'openid' => $this->user->openid,
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            // 超时重试
            return $this->wxPay($outTradeNo, $body, $price);
        }

        if (!is_string($prepayId)) {
            $this->error(json_encode($prepayId, JSON_UNESCAPED_UNICODE));
        }

        $json = Yii::$app->oil->wx->payment->configForPayment($prepayId);
        $this->sourceJs = [
            'order/index'
        ];
        $this->seo(['title' => '微信支付']);

        return $this->render('wx-pay', [
            'json' => $json,
            'order_number' => $outTradeNo
        ]);
    }

    /**
     * 微信支付回调
     */
    public function actionWxPaid()
    {
        Yii::info('微信支付回调修改订单状态');
        $payment = Yii::$app->oil->wx->payment;
        $response = $payment->handleNotify(function ($notify, $successful) {

            $result = $this->service('order.pay-handler', [
                'order_number' => $notify->out_trade_no,
                'paid_result' => $successful
            ]);

            if (is_string($result)) {
                Yii::error(Yii::t('common', $result));

                return $result;
            }
            $this->weChatTplMsgForPayment($result);

            return true;
        });

        return $response;
    }

    // --↓↓- Ali Payment -↓↓--

    /**
     * 支付宝下单
     *
     * @access  public
     * @link    http://www.kakehotels.com/order/ali?xxx
     * @license link create by $this->createSafeLink()
     * @return string
     */
    public function actionAli()
    {
        list($outTradeNo) = $this->localOrder(self::PAY_CODE_ALI, false);

        $url = $this->createSafeLink([
            'order_number' => $outTradeNo,
            'first' => true
        ], 'order/ali-pay', false);

        return $this->redirect($url);
    }

    /**
     * 支付宝支付订单（可重复调用）
     *
     * @link http://www.kakehotels.com/order/ali-pay?xxx
     * @return mixed
     */
    public function actionAliPay()
    {
        $params = $this->validateSafeLink(false);

        // 微信浏览器
        if (Helper::weChatBrowser()) {

            $this->mustLogin();

            $this->sourceCss = ['order/open-with-browser'];
            $this->sourceJs = ['order/index'];
            $this->seo(['title' => '支付宝支付']);

            return $this->render('open-with-browser', [
                'order_number' => $params['order_number'],
                'user_id' => $this->user->id
            ]);
        }

        $order = $this->getOrder($params['order_number'], 'order_number');

        // 查询订单
        $result = Yii::$app->oil->ali->alipayTradeQuery($order['order_number']);
        if (is_array($result)) {

            $stateInfo = [
                'WAIT_BUYER_PAY' => '交易创建，等待买家付款',
                'TRADE_CLOSED' => '未付款交易超时关闭，或支付完成后全额退款',
                'TRADE_SUCCESS' => '交易支付成功',
                'TRADE_FINISHED' => '交易结束，不可退款',
            ];

            if ($result['trade_status'] != 'WAIT_BUYER_PAY') {
                $this->error(Yii::t('common', 'resubmit the order please'));
            }

            // 生成订单编号
            $orderNumber = Helper::createOrderNumber(self::PAY_CODE_ALI, $order['user_id']);

            // 更新本地订单编号
            $result = $this->service('order.update-order-number', [
                'id' => $order['id'],
                'order_number' => $orderNumber
            ]);

            if (is_string($result)) {
                $this->error(Yii::t('common', $result));
            }

            // 关闭旧订单
            Yii::$app->oil->ali->alipayTradeClose($order['order_number']);
        }

        $notifyUrl = SCHEME . Yii::$app->params['frontend_url'] . '/order/ali-paid/';
        Yii::$app->oil->ali->alipayTradeWapPay([
            'subject' => $order['title'],
            'out_trade_no' => isset($orderNumber) ? $orderNumber : $order['order_number'],
            'total_amount' => intval($order['price']) / 100,
        ], $notifyUrl);

        return null;
    }

    /**
     * 支付宝轮询订单支付状态
     */
    public function actionAjaxPollOrder()
    {
        $params = Yii::$app->request->post();
        $result = $this->service('order.poll-order', $params);

        if ($result) {
            $router = [
                'order/pay-result',
                'order_number' => $params['order_number'],
                'payment_method' => 'ali'
            ];
            if ($channel = $this->params('channel')) {
                $router['channel'] = $channel;
            }
            $this->success(Url::toRoute($router));
        }
        $this->fail('order non-exists');
    }

    /**
     * 支付宝支付回调
     */
    public function actionAliPaid()
    {
        Yii::info('支付宝支付回调修改订单状态');
        $params = Yii::$app->request->post();
        Yii::info('支付宝异步回调数据：' . json_encode($params, JSON_UNESCAPED_UNICODE));

        if (empty($params)) {
            return null;
        }

        if (!Yii::$app->oil->ali->validateSignAsync($params)) {
            Yii::error('支付宝异步回调, 签名验证失败: ' . json_encode($params, JSON_UNESCAPED_UNICODE));
        }

        // 判断交易结果
        $successful = false;
        if (in_array($params['trade_status'], [
            'TRADE_SUCCESS',
            'TRADE_FINISHED'
        ])) {
            $successful = true;
        }

        $result = $this->service('order.pay-handler', [
            'order_number' => $params['out_trade_no'],
            'paid_result' => $successful
        ]);

        if (is_string($result)) {
            Yii::error(Yii::t('common', $result));

            return null;
        }

        $this->weChatTplMsgForPayment($result);
    }

    // --↓↓- Common -↓↓--

    /**
     * 支付结果页面
     *
     * @param string $order_number
     * @param string $payment_method
     *
     * @return string
     */
    public function actionPayResult($order_number, $payment_method = 'wx')
    {
        if (!in_array($payment_method, self::$paymentMethod)) {
            $this->error(Yii::t('common', 'param illegal', ['param' => 'payment_method']));
        }

        $this->sourceCss = null;
        $this->sourceJs = ['order/index'];

        return $this->render('pay-result', [
            'link_first' => Url::toRoute(['order/index']),
            'link_second' => $this->createSafeLink([
                'order_number' => $order_number
            ], 'order/' . $payment_method . '-pay/', $payment_method == 'wx' ? true : false)
        ]);
    }

    /**
     * 发送微信模板消息
     *
     * @access public
     *
     * @param array $result
     *
     * @return void
     */
    public function weChatTplMsgForPayment($result)
    {
        if (!empty($result['user_openid'])) {
            Yii::$app->oil->wx->sendTplMsg([
                'to' => $result['user_openid'],
                'tpl' => 'sURDDDE9mymmFni3-zKEyPmPl4pid3Ttf42rrnR_8ZI',
                'url' => Url::toRoute(['order/index'], true),
                'header' => '订单支付成功',
                'footer' => "如有疑问请联系客服 " . Yii::$app->params['company_tel']
            ], [
                $result['name'],
                $result['username'],
                '进入平台菜单 > 订单中心预约',
                $result['sub_total'],
                Helper::money($result['price'] / 100, '%s'),
            ]);
        }

        if (!empty($result['producer_openid'])) {
            Yii::$app->oil->wx->sendTplMsg([
                'to' => $result['producer_openid'],
                'tpl' => 'wUH-x5gnE6O8n9O8wAaFcHVDWhpf7DctTRqQDS-8BeA',
                'url' => Url::toRoute(['producer/order-list'], true),
                'header' => '您有新的分销订单产生',
                'footer' => "如有疑问请联系客服 " . Yii::$app->params['company_tel'],
            ], [
                date('Y-m-d H:i:s'),
                $result['payment_method'] ? '支付宝支付' : '微信支付',
                $result['order_number'],
                Helper::money($result['price'] / 100, '%s'),
            ]);
        }
    }
}