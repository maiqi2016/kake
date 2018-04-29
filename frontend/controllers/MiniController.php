<?php

namespace frontend\controllers;

use Yii;

/**
 * Mini program controller
 */
class MiniController extends GeneralController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'bind-phone'
        ])) {
            $action->controller->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionInitSession()
    {
        $this->success(['session_id' => Yii::$app->session->id]);
    }


    public function actionLogin($code)
    {
        $info = Yii::$app->oil->wx->mini_program->sns->getSessionKey($code);

        $user = $this->service(parent::$apiDetail, [
            'table' => 'user',
            'where' => [
                ['state' => 1],
                ['mpid' => $info->openid]
            ]
        ]);

        if (empty($user)) {
            $this->fail($info->openid);
        }

        $user['session_key'] = $info->session_key;

        $this->loginUser($user, 5, 'kake-mini-program');
        $user['session_id'] = Yii::$app->session->id;

        $this->success($user);
    }

    public function actionBindPhone()
    {
        $params = Yii::$app->request->post();

        if (empty($params['mpid'])) {
            $this->fail('参数异常，请重试');
        }

        $result = $this->service('user.mini-bind-or-register', $params);
        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success();
    }

    protected function encrypt($data)
    {
        return Yii::$app->oil->wx->mini_program->encryptor->decryptData($data);
    }
}