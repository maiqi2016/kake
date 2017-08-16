<?php

namespace frontend\controllers;

use frontend\components\SSO;
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
        $this->sourceJs = ['user/user'];

        return $this->render('apply-distributor');
    }

    /**
     * 单点登录 - demo
     */
    public function actionSso()
    {
        $token = Yii::$app->session->get('token');

        if (!$token) {

            $url = $this->currentUrl();
            if (!Yii::$app->request->get(SSO::$responseType)) {
                SSO::code($url);
            }

            $token = SSO::token($url)['access_token'];
            Yii::$app->session->set('token', $token);
        }

        $result = SSO::api('user.info', $token);

        $this->dump($result);
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
        SSO::logout('KK_SESS', $p['path'], $p['domain']);
    }
}
