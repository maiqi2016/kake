<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'order';
?>

<div class="content">
    <p class="title">支付结果提示</p>

    <div class="timeout" ng-init="paidLocation('<?= $link_first ?>')">{{second}}s</div>

    <a href="<?= $link_first ?>" class="buttons link active">查看我的订单</a>
    <a href="<?= $link_second ?>" class="buttons">重新支付</a>
</div>
