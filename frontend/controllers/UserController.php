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

    /**
     * 多媒体触发
     *
     * @param string $type
     * @param string $value
     *
     * @return string
     */
    public function actionMedia($type, $value)
    {
        $this->sourceJs = null;

        if (!in_array($type, ['tel', 'mailto'])) {
            $this->error('错误的媒体类型');
        }

        return $this->render('media', compact('type', 'value'));
    }
}
