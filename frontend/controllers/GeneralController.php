<?php

namespace frontend\controllers;

use Oil\src\Helper;
use common\controllers\MainController;
use yii\helpers\ArrayHelper;
use yii;

/**
 * General controller
 */
class GeneralController extends MainController
{
    /**
     * @cont string user info key
     */
    const USER = 'frontend_user_info';

    /**
     * @cont string reference
     */
    const REFERENCE = 'frontend_reference';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!empty(Yii::$app->params['upgrade'])) {
            return $this->redirect(['/general/upgrade']);
        }

        Yii::info('设置语言包');
        if (Yii::$app->session->has(self::LANGUAGE)) {
            Yii::$app->language = Yii::$app->session->get(self::LANGUAGE);
        }

        Yii::info('获取用户信息');
        if (!$this->user && Yii::$app->session->has(self::USER)) {
            $this->user = (object)Yii::$app->session->get(self::USER);
        }

        if (!in_array($this->module->requestedRoute, [
            // 首页
            '',
            'site/index',
            'site/ajax-list',
            // 列表页
            'items/index',
            'items/ajax-list',
            'items/region',
            // 阿里支付后处理
            'order/ali-paid',
            // 阿里支付
            'order/ali-pay',
            // 微信支付后处理
            'order/wx-paid',
            // 清除前台缓存
            'general/clear-cache',
            // 退出
            'user/logout',
            // 分销商小车引导页
            'distribution/index',
            // 微信自动回复
            'we-chat/reply',
            // 商品详情
            'detail/index',
            // 小程序
            'mini/init-session',
            'mini/login',
            'mini/bind-phone',
        ])
        ) {
            $this->mustLogin();
        }

        Yii::$app->view->params['user_info'] = $this->user;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'ajax-sms'
        ])) {
            $action->controller->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * 静态化文件
     *
     * @access public
     *
     * @param string $action
     *
     * @return array
     */
    public function htmlFile($action = null)
    {
        $params = array_merge(Yii::$app->request->post(), Yii::$app->request->get());
        unset($params['r'], $params[Yii::$app->request->csrfParam]);

        $controller = $this->id;
        $action = $action ?: $this->action->id;
        $key = empty($params) ? null : '.' . md5(json_encode($params));

        return [
            Yii::getAlias("@runtime/static/{$controller}.{$action}{$key}.html"),
            $controller,
            $action,
            $key
        ];
    }

    /**
     * 生成静态文件
     *
     * @access public
     *
     * @param string $content
     * @param array $file
     *
     * @return bool
     */
    public function createHtmlFile($content, $file = null)
    {
        list($file, $ctl, $act) = $file ?: $this->htmlFile();
        $path = dirname($file);

        !is_dir($path) && Helper::createDirectory($path);

        $result = Helper::writeFile($file, Helper::compressHtml($content));

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function runAction($id, $params = [])
    {
        unset($_GET['table'], $_GET['from']);

        list($static) = $this->htmlFile($id ?: $this->defaultAction);
        if (Yii::$app->params['html_static'] && file_exists($static)) {
            exit(file_get_contents($static));
        }

        return parent::runAction($id, $params);
    }

    /**
     * 登录用户
     *
     * @param array $user
     * @param string $type
     * @param string $system
     */
    protected function loginUser($user, $type, $system = 'kake')
    {
        Yii::info("将用户信息设置到 Session 中 - 来自 <{$system}> 系统的 <{$type}> 类型登录");

        Yii::$app->session->set(self::USER, $user);
        $this->user = (object)array_merge((array)$this->user, $user);

        $this->service('user.login-log', [
            'id' => $user['id'],
            'ip' => Yii::$app->request->userIP,
            'type' => $type
        ]);
    }

    /**
     * 需要登录
     *
     * @access public
     * @return void
     * @throws \Exception
     */
    public function mustLogin()
    {
        if ($this->user) {
            return;
        }

        // ajax
        if (Yii::$app->request->isAjax) {
            $this->fail('login first');
        }

        $ssoLogin = function ($uid = null) {
            $extra = $uid ? ['id' => $uid] : [];
            $url = Helper::unsetParamsForUrl('code', $this->currentUrl());
            $result = Yii::$app->oil->sso->auth($url, $extra);
            if (is_string($result)) {
                $this->redirect([
                    '/general/error',
                    'message' => urlencode(Yii::t('common', $result))
                ]);
            } else {
                $this->loginUser($result, 'sso-login');
            }
        };

        // normal
        if (Helper::weChatBrowser()) {

            // 授权请求
            if (Yii::$app->request->get('code')) {
                $result = Yii::$app->oil->wx->user();
                if (!empty($result['nickname'])) {
                    $result['nickname'] = Helper::filterEmjoy($result['nickname']);
                }
                $result = $this->service('user.get-with-we-chat', $result);
                if (is_string($result)) {
                    $this->redirect([
                        '/general/error',
                        'message' => urlencode(Yii::t('common', $result))
                    ]);
                } else {
                    if (empty($result['phone'])) {
                        $ssoLogin($result['id']);
                    } else {
                        $this->loginUser($result, isset($result['state']) ? 'we-chat-login' : 'we-chat-bind');
                    }
                }
            } else {
                Yii::$app->oil->wx->config('oauth.callback', $this->currentUrl());
                Yii::$app->oil->wx->auth();
            }
        } else {
            $ssoLogin();
        }
    }

    /**
     * 获取参数
     *
     * @access public
     *
     * @param mixed $keys
     *
     * @return mixed
     */
    public function params($keys)
    {
        $p = array_merge(Yii::$app->request->post(), Yii::$app->request->get());
        $params = [];

        foreach ((array)$keys as $item) {
            $params[$item] = isset($p[$item]) ? $p[$item] : null;
        }

        if (empty($params)) {
            return false;
        }

        return is_array($keys) ? $params : current($params);
    }

    /**
     * 创建安全链接
     *
     * @access protected
     *
     * @param mixed $params
     * @param string $router
     * @param boolean $checkUser
     *
     * @return string
     */
    protected function createSafeLink($params, $router, $checkUser = true)
    {
        $item = [
            'item' => $params,
            'time' => TIME
        ];

        if ($checkUser) {
            $item['user_id'] = $this->user->id;
        }

        $item = Helper::createSign($item, 'sign');
        $item = base64_encode(Yii::$app->oil->rsa->encryptByPublicKey(json_encode($item)));

        $url = Helper::joinString('/', Yii::$app->params['frontend_url'], $router) . '/';

        $channel = $this->params('channel');
        $channel = $channel ? "&channel={$channel}" : null;

        return "{$url}?safe={$item}{$channel}";
    }

    /**
     * 验证安全链接
     *
     * @access protected
     *
     * @param boolean $checkUser
     *
     * @return mixed
     */
    protected function validateSafeLink($checkUser = true)
    {
        $item = base64_decode(Yii::$app->request->get('safe'));
        $item = json_decode(Yii::$app->oil->rsa->decryptByPrivateKey($item), true);

        $error = false;
        if (!$error && !$item) {
            $error = '非法的支付链接';
        }

        if (!$error && !Helper::validateSign($item, 'sign')) {
            $error = '支付链接签名错误';
        }

        $timeout = Yii::$app->params['order_pay_timeout'] * MINUTE;
        if (!$error && (empty($item['time']) || TIME - $item['time'] > $timeout)) {
            $error = '支付链接已经超时';
        }

        if (!$error && $checkUser && $this->user->id != $item['user_id']) {
            $error = '非法他人代付';
        }

        if ($error) {
            Yii::error('支付链接异常: ' . json_encode($item, JSON_UNESCAPED_UNICODE));
            $this->error(Yii::t('common', 'payment link illegal'));
        }

        return $item['item'];
    }

    /**
     * 获取产品详情
     *
     * @access public
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function getProduct($id)
    {
        $id = (int)$id;

        if (empty($id)) {
            return 'product id required';
        }

        return $this->cache([
            'get.product',
            func_get_args()
        ], function () use ($id) {

            $controller = $this->controller('product');
            $condition = $this->callMethod('editCondition', [], null, $controller);

            $condition = ArrayHelper::merge($condition, [
                'where' => [
                    ['product.id' => $id],
                    ['product.state' => 1],
                ]
            ]);

            $detail = $this->service('product.detail', $condition);
            if (empty($detail)) {
                return 'product does not exist';
            }

            $detail = $this->callMethod('sufHandleField', $detail, [
                $detail,
                'detail'
            ], $controller);
            if (empty($detail['package'])) {
                return 'product package illegal';
            }

            if (!empty($detail)) {
                $field = $detail['sale'] ? 'sale_price' : 'price';
                if ($detail['real_sales'] > $detail['virtual_sales']) {
                    $detail['max_sales'] = $detail['real_sales'];
                } else {
                    $detail['max_sales'] = $detail['virtual_sales'] + $detail['real_sales'];
                }

                foreach ($detail['package'] as $key => $item) {
                    if (empty($item['bidding'])) {
                        unset ($detail['package'][$key]);
                    }
                }

                if (empty($detail['package'])) {
                    return 'product package illegal';
                }

                $detail['min_price'] = min(array_column($detail['package'], $field));
            }

            if (empty($detail['min_price']) || $detail['min_price'] <= 0) {
                return 'product price error';
            }

            return $detail;
        }, DAY, null, Yii::$app->params['use_cache']);
    }

    /**
     * 列表产品焦点图
     *
     * @access public
     *
     * @param integer $limit
     *
     * @return array
     */
    public function listProductFocus($limit)
    {
        return $this->cache([
            'list.focus',
            func_get_args()
        ], function () use ($limit) {
            $controller = $this->controller('product');
            $condition = [
                'join' => [
                    [
                        'table' => 'attachment',
                        'as' => 'cover',
                        'left_on_field' => 'attachment_cover',
                    ],
                ],
                'select' => [
                    'cover.deep_path AS cover_deep_path',
                    'cover.filename AS cover_filename',
                    'product.id',
                    'product.sort',
                    'product.attachment_cover',
                    'product.update_time'
                ],
                'order' => [
                    'ISNULL(product.sort), product.sort ASC',
                    'product.update_time DESC'
                ],
                'where' => [
                    ['product.manifestation' => 1],
                    ['product.state' => 1]
                ],
                'limit' => $limit,
            ];
            $list = $this->service('product.list', $condition);
            array_walk($list, function (&$item) use ($controller) {
                $item = $this->createAttachmentUrl($item, ['attachment_cover' => 'cover']);
            });

            return $list;
        }, DAY, null, Yii::$app->params['use_cache']);
    }

    /**
     * 列表板块
     *
     * @access public
     *
     * @param int $limit
     *
     * @return array
     */
    public function listPlate($limit = 0)
    {
        return $this->cache('list.plate.' . $limit, function () use ($limit) {
            $list = $this->service(parent::$apiList, [
                'table' => 'product_plate',
                'where' => [
                    ['product_plate.state' => 1]
                ],
                'join' => [
                    ['table' => 'attachment']
                ],
                'select' => [
                    'product_plate.id',
                    'product_plate.name',
                    'product_plate.attachment_id',
                    'attachment.deep_path',
                    'attachment.filename',
                ],
                'order' => [
                    'ISNULL(product_plate.sort), product_plate.sort ASC',
                    'product_plate.update_time DESC'
                ],
                'limit' => $limit
            ]);

            $controller = $this->controller('product-plate');
            array_walk($list, function (&$item) use ($controller) {
                $item = $this->callMethod('sufHandleField', $item, [$item], $controller);
            });

            return $list;
        }, MONTH, null, Yii::$app->params['use_cache']);
    }

    /**
     * 列表地区
     *
     * @access public
     *
     * @param mixed $plate
     * @param int $limit
     *
     * @return array
     */
    public function listRegion($plate = [], $limit = 0)
    {
        return $this->cache([
            'list.region',
            func_get_args()
        ], function () use ($plate, $limit) {

            $where = [['product_region.state' => 1]];
            if (!empty($plate)) {
                $where[] = ['product_region.product_plate_id' => (array)$plate];
            }

            $list = $this->service(parent::$apiList, [
                'table' => 'product_region',
                'where' => $where,
                'join' => [
                    ['table' => 'attachment']
                ],
                'select' => [
                    'product_region.id',
                    'product_region.name',
                    'product_region.attachment_id',
                    'attachment.deep_path',
                    'attachment.filename',
                ],
                'order' => [
                    'ISNULL(product_region.sort), product_region.sort ASC',
                    'product_region.update_time DESC'
                ],
                'limit' => $limit
            ]);

            $controller = $this->controller('product-region');
            array_walk($list, function (&$item) use ($controller) {
                $item = $this->callMethod('sufHandleField', $item, [$item], $controller);
            });

            return $list;
        }, MONTH, null, Yii::$app->params['use_cache']);
    }

    /**
     * 列表板块和下级地区
     *
     * @access public
     * @return array
     */
    public function listPlateAndRegion()
    {
        return $this->cache('list.plate.and.region', function () {

            $list = $this->service(parent::$apiList, [
                'table' => 'product_region',
                'where' => [
                    ['product_region.state' => 1]
                ],
                'join' => [
                    ['table' => 'product_plate']
                ],
                'select' => [
                    'product_plate.name AS product_plate_name',
                    'product_region.id',
                    'product_region.name',
                ],
                'order' => [
                    'ISNULL(product_region.sort), product_region.sort ASC',
                    'product_region.update_time DESC'
                ]
            ]);

            $_list = [];
            foreach ($list as $item) {
                $_list[$item['product_plate_name']][$item['id']] = $item['name'];
            }

            $key = '其他';
            if (isset($_list[$key])) {
                $val = $_list[$key];
                unset($_list[$key]);
                $_list[$key] = $val;
            }

            return $_list;
        }, MONTH, null, Yii::$app->params['use_cache']);
    }

    /**
     * 列表产品
     *
     * @access public
     *
     * @param integer $page
     * @param integer $pageSize
     * @param integer $time
     * @param array $options
     *
     * @return array
     */
    public function listProduct($page = 1, $pageSize = null, $time = DAY, $options = [])
    {
        return $this->cache([
            'list.product',
            func_get_args()
        ], function () use ($page, $pageSize, $time, $options) {
            $where = [];

            $condition = DetailController::$productListCondition;

            // 具体 id 列表
            if (!empty($options['ids'])) {
                $ids = is_array($options['ids']) ? $options['ids'] : explode(',', $options['ids']);
                $where[] = ['product.id' => $ids];
                $condition['order'] = 'field(product.id, ' . implode(',', $ids) . ')';
            }

            // 表现方式
            if (isset($options['manifestation']) && is_numeric($options['manifestation'])) {
                $where[] = ['product.manifestation' => $options['manifestation']];
            }

            // 分类
            if (isset($options['classify']) && is_numeric($options['classify'])) {
                $where[] = ['product_upstream.classify' => $options['classify']];
            }

            // 折扣中
            if (isset($options['sale'])) {
                $controller = $this->controller('product');
                $_where = $this->callStatic('saleReverseWhereLogic', [], [$options['sale'] ? 1 : 0], $controller);
                $where = array_merge($where, $_where);
            }

            // 板块
            $plate = [];
            if (isset($options['plate']) && is_numeric($options['plate'])) {
                $plate = $this->getRegionByPlate($options['plate']) ?: [0];
            }

            // 地区
            if (empty($options['region'])) {
                $options['region'] = $plate;
            } else {
                $options['region'] = array_merge(explode(',', $options['region']), $plate);
            }

            if (!empty($options['region'])) {
                $where[] = ['product_region.id' => $options['region']];
            }

            // 产品上游 id
            if (!empty($options['upstream'])) {
                $ids = is_array($options['upstream']) ? $options['upstream'] : explode(',', $options['upstream']);
                $where[] = ['product_upstream.id' => $ids];
            }

            // 关键字
            if (!empty($options['keyword'])) {
                $where[] = [
                    'or',
                    [
                        'like',
                        'product.title',
                        $options['keyword']
                    ],
                    [
                        'like',
                        'product_region.name',
                        $options['keyword']
                    ],
                    [
                        'like',
                        'product_upstream.name',
                        $options['keyword']
                    ]
                ];
            }

            $condition['where'] = array_merge($condition['where'], $where);

            if (!empty($options['hot'])) {
                $condition['order'] = '(product.virtual_sales + product.real_sales) DESC';
            }

            $pageParams = Helper::page($page, $pageSize ?: Yii::$app->params['product_page_size']);
            list($condition['offset'], $condition['limit']) = $pageParams;

            $controller = $this->controller('product-package');
            $list = $this->service('product.list', $condition);
            foreach ($list as $key => &$item) {
                if (empty($item['price'])) {
                    // unset($list[$key]);
                    // continue;
                }
                $item = $this->callMethod('sufHandleField', $item, [$item], $controller);
                $item = $this->createAttachmentUrl($item, ['attachment_cover' => 'cover']);
                $item['max_sales'] = max($item['virtual_sales'], $item['real_sales']);
                $item['min_price'] = $item['price'];
                if (!empty($item['sale_price'])) {
                    $item['min_price'] = min($item['sale_price'], $item['price']);
                }
            }

            return $list;
        }, $time, null, Yii::$app->params['use_cache']);
    }

    /**
     * 通过渠道号获取分销商信息
     *
     * @access public
     *
     * @param string $channel
     *
     * @return array
     */
    public function getProducerByChannel($channel)
    {
        $uid = Helper::integerDecode($channel);
        if (!$uid) {
            return [
                Yii::t('common', 'distributor params illegal'),
                $uid
            ];
        }

        // 获取分销商信息
        $producer = $this->getProducer($uid);
        if (empty($producer)) {
            return [
                Yii::t('common', 'distributor params illegal'),
                $uid
            ];
        }

        return [
            $producer,
            $uid
        ];
    }

    /**
     * 获取分销商的产品
     *
     * @access public
     *
     * @param integer $producer_id
     * @param integer $classify
     * @param integer $page
     * @param integer $limit
     *
     * @return array
     */
    public function listProducerProduct($producer_id, $classify = null, $page = null, $limit = null)
    {
        list($offset, $limit, $page) = Helper::page($page, $limit);

        $product = $this->service('producer.list-product-ids', [
            'producer_id' => $producer_id,
            'classify' => $classify,
            'page_number' => $page,
            'limit' => ($limit ?: Yii::$app->params['distribution_items_limit'])
        ]);
        if (empty($product)) {
            return $product;
        }

        $product = $this->listProduct(1, $limit, DAY, ['ids' => $product]);

        return $product;
    }

    /**
     * List hotels
     *
     * @access public
     *
     * @param callable $handler
     *
     * @return array
     */
    public function listUpstreams($handler)
    {
        $upstream = $this->service(parent::$apiList, [
            'table' => 'product_upstream',
            'select' => [
                'id',
                'name'
            ],
            'where' => [
                ['state' => 1]
            ]
        ]);

        if (is_callable($handler)) {
            $upstream = array_map($handler, $upstream);
        }

        return $upstream;
    }

    /**
     * 通过板块获取涵盖地区
     *
     * @access public
     *
     * @param  integer $plate
     * @param boolean $nameModel Default id
     *
     * @return array
     */
    public function getRegionByPlate($plate, $nameModel = false)
    {
        $map = $this->cache('list.region.' . $plate, function () {
            $result = $this->service(self::$apiList, [
                'table' => 'product_region',
                'where' => [['state' => 1]],
                'select' => [
                    'id',
                    'product_plate_id',
                    'name'
                ]
            ]);

            if (!is_array($result) || empty($result)) {
                return [];
            }

            $_map = [];
            foreach ($result as $item) {
                $_map[$item['product_plate_id']][$item['id']] = $item['name'];
            }

            return $_map;
        }, MONTH, null, Yii::$app->params['use_cache']);

        $map = empty($map[$plate]) ? [] : $map[$plate];

        return $nameModel ? array_values($map) : array_keys($map);
    }

    /**
     * 列表产品套餐
     *
     * @access public
     *
     * @param integer $product_id
     *
     * @return array
     */
    public function listProductPackage($product_id)
    {
        $product_id = (int)$product_id;
        if (empty($product_id)) {
            $this->error(Yii::t('common', 'product package id required'));
        }

        $list = $this->service('product.package-list', ['product_id' => $product_id]);

        $purchaseTimes = [];
        if ($this->user) {
            $purchaseTimes = $this->service('order.purchase-times', [
                'user_id' => $this->user->id,
                'package_ids' => array_column($list, 'id')
            ], 'yes');
        }

        $controller = $this->controller('product-package');
        array_walk($list, function (&$item) use ($controller, $purchaseTimes) {

            $limit = 'purchase_limit';
            $mLimit = 'min_purchase_limit';

            if ($item[$limit] <= 0) {
                $item[$mLimit] = -1;
            } else {
                $item[$mLimit] = $item[$limit];
                if (isset($purchaseTimes[$item['id']])) {
                    $item[$mLimit] = $item[$limit] - $purchaseTimes[$item['id']];
                    $item[$mLimit] = $item[$mLimit] <= 0 ? 0 : $item[$mLimit];
                }
            }

            $item = $this->callMethod('sufHandleField', $item, [$item], $controller);
            $item['min_price'] = $item['price'];
            if (!empty($item['sale_price'])) {
                $item['min_price'] = min($item['sale_price'], $item['price']);
            }
        });

        list($list) = Helper::valueToKey($list, 'id');

        return $list;
    }

    /**
     * 列表产品套餐打包
     *
     * @access public
     *
     * @param integer $product_id
     *
     * @return array
     */
    public function listProductPackageBind($product_id)
    {
        $list = $this->service('product.package-bind-list', ['product_id' => $product_id]);
        $handler = function ($list) use (&$handler) {

            if (empty($list)) {
                return [];
            }

            $list = $old = array_values(array_map('array_values', $list));
            $focus = array_shift($list);

            $over = true;
            foreach ($list as $key => $item) {
                if (array_intersect($focus, $item)) {
                    $focus = array_unique(array_merge($focus, $item));
                    unset($list[$key]);
                    $over = false;
                }
            }

            if ($over) {
                return array_merge([$focus], $list);
            }

            return $handler(array_merge([$focus], $list));
        };

        return $handler($list);
    }

    /**
     * 列表子订单
     *
     * @access public
     *
     * @param integer $page
     * @param mixed $state
     * @param integer $page_size
     *
     * @return array
     */
    public function listOrderSub($page = 1, $state = null, $page_size = null)
    {
        $where = [
            ['order.user_id' => $this->user->id]
        ];

        if (is_numeric($state)) {
            $where[] = ['order_sub.state' => $state];
        } else if (is_array($state)) {
            $where[] = [
                'in',
                'order_sub.state',
                $state
            ];
        }

        $condition = OrderController::$orderSubCondition;

        if (!empty($condition['where'])) {
            $where = array_merge($condition['where'], $where);
        }
        $condition['where'] = $where;

        list($condition['offset'], $condition['limit']) = Helper::page($page,
            $page_size ?: Yii::$app->params['order_page_size']);
        $list = $this->service('order.list', $condition);

        $controller = $this->controller('order');
        array_walk($list, function (&$item) use ($controller) {
            $item['code'] = wordwrap($item['code'], 4, ' ', true);
            $item = $this->callMethod('sufHandleField', $item, [$item], $controller);
            $item = $this->createAttachmentUrl($item, ['attachment_cover' => 'cover']);
        });

        return $list;
    }

    /**
     * 列表广告
     *
     * @access public
     *
     * @param integer $type
     * @param integer $limit
     *
     * @return array
     */
    public function listAd($type, $limit = null)
    {
        return $this->cache([
            'list.ad',
            func_get_args()
        ], function () use ($type, $limit) {
            $controller = $this->controller('ad');
            $condition = $this->callMethod('indexCondition', [], null, $controller);

            $condition = ArrayHelper::merge($condition, [
                'where' => [
                    ['ad.state' => 1],
                    ['ad.type' => $type],
                    [
                        '<',
                        'ad.from',
                        date('Y-m-d H:i:s', TIME)
                    ],
                    [
                        '>',
                        'ad.to',
                        date('Y-m-d H:i:s', TIME)
                    ]
                ],
                'limit' => $limit
            ]);

            $adList = $this->service('general.list-ad', $condition, 'yes');
            array_walk($adList, function (&$item) use ($controller) {
                $item = $this->callMethod('sufHandleField', $item, [$item], $controller);
            });

            return $adList;
        }, HOUR, null, Yii::$app->params['use_cache']);
    }

    /**
     * 渲染模态框
     *
     * @access public
     *
     * @param string $view
     * @param array $params
     * @param string $title
     *
     * @return bool
     */
    public function modal($view, $params = [], $title = null)
    {
        $content = $this->renderPartial($view, $params);

        $response = ['message' => $content];
        $title && $response['title'] = $title;

        $this->success($response);

        return true;
    }

    /**
     * 清理缓存
     */
    public function actionClearCache()
    {
        $this->ipa(function () {
            return Yii::$app->cache->flush();
        });
    }

    /**
     * @inheritdoc
     */
    public function render($view, $params = [], $static = false)
    {
        if (Yii::$app->request->isAjax) {
            $this->success($params);
        }

        Yii::info('开始渲染模板文件');
        $html = parent::render($view, $params);
        Yii::info('完成渲染模板文件');
        if ($static && Yii::$app->params['html_static']) {
            Yii::info('开始生成当前页面静态化文件');
            $this->createHtmlFile($html);
        }

        return $html;
    }

    /**
     * 系统维护页面
     *
     * @access public
     * @return mixed
     */
    public function actionUpgrade()
    {
        $params = Yii::$app->params;
        if (!$params['upgrade']) {
            return $this->redirect(['site/index']);
        }

        $message = sprintf($params['upgrade_message'], $params['upgrade_minute']);
        $this->message($message, $params['upgrade_title']);

        return null;
    }
}