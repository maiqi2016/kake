<?php

namespace frontend\controllers;

use common\components\Helper;
use Yii;
use yii\helpers\Url;

/**
 * Producer controller
 */
class ProducerController extends GeneralController
{
    /**
     * @var array Avatar config
     */
    public static $avatar = [
        'suffix' => [
            'png',
            'jpg',
            'jpeg'
        ],
        'pic_sizes' => '256-MAX*256-MAX',
        'max_size' => 1024 * 5
    ];

    /**
     * 菜单
     */
    public function actionIndex()
    {
        $this->sourceCss = null;
        $this->sourceJs = false;
        $producer = $this->getProducer($this->user->id);
        if (empty($producer)) {
            Yii::$app->session->setFlash('message', '请先完善个人资料');

            return $this->redirect(['producer/setting']);
        }
        $this->seo(['title' => '分销商管理']);

        return $this->render('index', compact('producer'));
    }

    /**
     * 个人设置
     */
    public function actionSetting()
    {
        $this->sourceCss = null;
        $this->sourceJs = [
            'producer/setting',
            'jquery.ajaxupload'
        ];
        $producer = $this->getProducer($this->user->id);
        if (empty($producer)) {
            $producer['logo_preview_url'] = [
                Yii::$app->params['frontend_source'] . '/img/logo.png'
            ];
        }
        $angular = Helper::pullSome($producer, [
            'id',
            'name',
            'logo_attachment_id',
            'account_type',
            'account_number'
        ]);
        $this->seo(['title' => '分销商管理']);

        return $this->render('setting', compact('producer', 'angular'));
    }

    /**
     * ajax 编辑个人资料
     */
    public function actionAjaxEditSetting()
    {
        $profile = Yii::$app->request->post();
        $result = $this->service(parent::$apiNewlyOrEdit, array_merge($profile, [
            'table' => 'producer_setting',
            'where' => [
                'id' => empty($profile['id']) ? 0 : $profile['id'],
                'producer_id' => $this->user->id,
                'state' => 1
            ]
        ]));

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(Url::to(['producer/index']));
    }

    /**
     * 获取二维码
     */
    public function actionQrCode()
    {
        $this->sourceCss = null;
        $this->sourceJs = false;

        $data = $this->controller('producer-setting')->spreadInfo($this->user->id);
        if (empty($data)) {
            Yii::$app->session->setFlash('message', '请先完善个人资料');

            return $this->redirect(['producer/setting']);
        }
        $this->seo(['title' => '分销商管理']);

        return $this->render('qr-code', compact('data'));
    }

    /**
     * 获取推广链接
     */
    public function actionLink()
    {
        $this->sourceCss = null;
        $this->sourceJs = [
            '/node_modules/clipboard/dist/clipboard.min'
        ];
        $channel = Helper::integerEncode($this->user->id);

        $url = function ($router) use ($channel) {
            $url = Yii::$app->params['frontend_url'];
            $url .= Url::toRoute([
                $router,
                'channel' => $channel
            ]);

            return urldecode($url);
        };
        $links = [
            $url('distribution/index'),
            $url('distribution/items')
        ];
        $this->seo(['title' => '分销商管理']);

        return $this->render('link', compact('links'));
    }

    /**
     * 分销产品列表
     */
    public function actionProductList()
    {
        $this->sourceCss = null;
        $this->sourceJs = [
            '/node_modules/clipboard/dist/clipboard.min'
        ];

        $controller = $this->controller('producer-product');
        $controller::$uid = $this->user->id;
        $list = $controller->showList('my', true, false, [
            'size' => 0
        ])[0];
        $this->seo(['title' => '分销商管理']);

        return $this->render('product-list', compact('list'));
    }

    /**
     * 分销记录列表
     */
    public function actionOrderList()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        $controller = $this->controller('producer-log');
        $controller::$uid = $this->user->id;
        $list = $controller->showList('my', true, false, [
            'size' => 0
        ])[0];

        $total = count($list);
        $quota = Helper::money(array_sum(array_column($list, 'commission_quota')), '%s');
        $this->seo(['title' => '分销商管理']);

        return $this->render('order-list', compact('list', 'total', 'quota'));
    }

    /**
     * 提现
     */
    public function actionWithdraw()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        $record = $this->controller('producer-quota')->showFormWithRecord([
            'producer_id' => $this->user->id,
            'state' => 1
        ], 'my', true);

        $quota = Helper::money(empty($record) ? 0 : $record['quota'], '%s');
        $producer = $this->getProducer($this->user->id);
        if (empty($producer)) {
            Yii::$app->session->setFlash('message', '请先完善个人资料');

            return $this->redirect(['producer/setting']);
        }
        $this->seo(['title' => '分销商管理']);

        return $this->render('withdraw', compact('quota', 'producer'));
    }

    /**
     * ajax 申请提现
     */
    public function actionAjaxApplyWithdraw()
    {
        $quota = Yii::$app->request->post('quota');
        $result = $this->controller('producer-quota')->applyWithdraw($this->user->id, $quota);

        if (is_string($result)) {
            $this->fail($result);
        }

        $result = $this->service(parent::$apiNewly, [
            'table' => 'producer_withdraw',
            'producer_id' => $this->user->id,
            'withdraw' => $quota * 100
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(Url::to(['producer/index']));
    }

    /**
     * 申请成为分销商
     *
     * @access public
     * @return string
     */
    public function actionApplyDistributor()
    {
        $this->sourceCss = null;
        $this->sourceJs = [
            'producer/apply-distributor',
            'jquery.ajaxupload'
        ];

        $this->seo(['title' => '加入喀客平台']);

        return $this->render('apply-distributor');
    }

    /**
     * ajax 上传头像
     */
    public function actionUploadAvatar()
    {
        $this->uploader(self::$avatar);
    }

    /**
     * ajax 上传头像 - 自动裁剪
     */
    public function actionUploadAvatarCrop()
    {
        $result = $this->uploader(self::$avatar, null, false);

        $url = Yii::$app->params['upload_path'];
        $img = Helper::joinString('/', $url, $result['deep_path'], $result['filename']);
        $this->thumbCrop($img, 256, 256, true);

        $this->success($result);
    }

    /**
     * Ajax 申请成为分销商
     */
    public function actionAjaxApplyDistributor()
    {
        try {
            $data = Helper::pullSome(Yii::$app->request->post(), [
                'phone',
                'name',
                'attachment' => 'attachment_id'
            ]);
        } catch (\Exception $e) {
            $this->fail('abnormal params');
        }

        /**
         * @var $data array
         */
        $result = $this->service(parent::$apiNewlyOrEdit, array_merge($data, [
            'table' => 'producer_apply',
            'where' => [
                'user_id' => $this->user->id,
            ],
            'state' => 1
        ]));

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(Url::to(['site/index']));
    }
}
