<?php

namespace frontend\controllers;

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
}
