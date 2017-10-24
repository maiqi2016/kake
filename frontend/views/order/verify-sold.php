<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'order';
?>

<header>
    核销中心
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body">
    <form action="">
        <input type="hidden" name="r" value="order/verify-sold">
        <input type="text" name="sold" id="hxm" placeholder="请在此输入核销码" ng-model="code" maxlength="14">
        <button class="n1">确认核销</button>
    </form>
    <button class="n2 lift-scan">扫描二维码核销</button>
</div>