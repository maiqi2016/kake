<?php

namespace frontend\controllers;

use common\components\Helper;
use common\components\SsoClient;
use Yii;

/**
 * User controller
 */
class UserController extends GeneralController
{
    /**
     * 申请成为分销商
     *
     * @access public
     * @return string
     */
    public function actionApplyDistributor()
    {
        $this->mustLogin();

        $this->sourceCss = ['user/user'];
        $this->sourceJs = [
            'user/user',
            'jquery.ajaxupload'
        ];

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
     * 单点登录 - demo
     */
    public function actionSso()
    {
        $user = SsoClient::auth();

        $this->dump($user);
    }

    /**
     * 退出登录
     *
     * @access public
     * @return void
     */
    public function actionLogout()
    {
        $p = Yii::$app->session->cookieParams;
        SsoClient::logout('KK_SESS', $p['path'], $p['domain']);
    }
}
