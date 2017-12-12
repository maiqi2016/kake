<?php

namespace frontend\controllers;

use Yii;

/**
 * User controller
 */
class UserController extends GeneralController
{
    /**
     * 退出登录
     *
     * @access public
     * @return void
     */
    public function actionLogout()
    {
        $p = Yii::$app->session->cookieParams;
        Yii::$app->oil->sso->logout('KK_SESS', $p['path'], $p['domain']);
    }
}
