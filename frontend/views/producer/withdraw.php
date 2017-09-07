<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<header>
    佣金提现页面
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body">
    <div class="blank" style="margin-top:40px"></div>

    <div class="rest">
        <div class="rest-money common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/record.svg">
            <p class="txt">佣金余额（元）</p>
        </div>
    </div>

    <div class="blank"></div>

    <div class="withdraw">
        <div class="acount common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/record.svg">
        </div>
    </div>

    
</div>