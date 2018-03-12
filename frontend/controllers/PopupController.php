<?php

namespace frontend\controllers;

use Yii;

/**
 * Popup controller
 */
class PopupController extends GeneralController
{
    /**
     * 弹窗显示抽奖码
     */
    public function actionLotteryCode()
    {
        $params = Yii::$app->request->post();

        return $this->modal('lottery-code', ['msg' => base64_decode($params['msg'])]);
    }
}