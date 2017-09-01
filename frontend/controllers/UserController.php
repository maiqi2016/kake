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
