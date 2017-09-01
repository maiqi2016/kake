<?php

namespace frontend\controllers;

use common\components\Helper;
use Yii;

/**
 * Producer controller
 */
class ProducerController extends GeneralController
{
    /**
     * 菜单
     */
    public function actionIndex()
    {
        $this->sourceCss = null;
        $this->sourceJs = false;
        $producer = $this->getProducer($this->user->id);

        return $this->render('index', compact('producer'));
    }

    /**
     * 个人设置
     */
    public function actionSetting()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;
        $producer = $this->getProducer($this->user->id);

        return $this->render('setting', compact('producer'));
    }

    /**
     * 获取二维码
     */
    public function actionQrCode()
    {
        $this->sourceCss = null;
        $this->sourceJs = false;
        $data = $this->controller('producer-setting')->spreadInfo($this->user->id);

        return $this->render('qr-code', compact('data'));
    }

    /**
     * 获取推广链接
     */
    public function actionLink()
    {
        $this->sourceCss = null;
        $this->sourceJs = false;
        $channel = Helper::integerEncode($this->user->id);

        return $this->render('link', compact('channel'));
    }

    /**
     * 分销产品列表
     */
    public function actionProductList()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        return $this->render('product-list');
    }

    /**
     * 分销记录列表
     */
    public function actionOrderList()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        return $this->render('order-list');
    }

    /**
     * 提现
     */
    public function actionWithdraw()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        return $this->render('withdraw');
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

        $this->seo(['title' => '加入喀客KAKE']);

        return $this->render('apply-distributor');
    }

    /**
     * ajax 上传头像
     */
    public function actionUploadAvatar()
    {
        $this->uploader([
            'suffix' => [
                'png',
                'jpg',
                'jpeg'
            ],
            'pic_sizes' => '128-MAX*128-MAX',
            'max_size' => 1024 * 5
        ]);
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
        $result = $this->service('general.newly-or-edit', array_merge([
            'table' => 'producer_apply',
            'where' => [
                'user_id' => $this->user->id,
            ],
            'state' => 1
        ], $data));

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success($result);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->mustLogin();

        return parent::beforeAction($action);
    }
}
