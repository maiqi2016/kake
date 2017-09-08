<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<header>
    分销产品列表
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="list" ng-init="clipboard()">
    <div class="product">
        <div class="img">
            <img src="<?= $params['frontend_source'] ?>/img/producer/card.png"/>
        </div>
        <div class="txt">
            <p class="t1">恒大海上威尼斯 邂逅那一片“双色海”</p>
            <p class="t2">酒店名称：启东 | 恒大海上威尼斯酒店</p>
            <p class="t2">分佣金额：7.9￥起</p>
            <div class="copy" data-clipboard-text="amy clipboard.js"><img src="<?= $params['frontend_source'] ?>/img/producer/icon/copy-link.svg"></div>
        </div>
    </div>
    <div class="product">
        <div class="img">
            <img src="<?= $params['frontend_source'] ?>/img/producer/card.png"/>
        </div>
        <div class="txt">
            <p class="t1">恒大海上威尼斯 邂逅那一片“双色海”</p>
            <p class="t2">酒店名称：启东 | 恒大海上威尼斯酒店</p>
            <p class="t2">分佣金额：7.9￥起</p>
            <div class="copy"><img src="<?= $params['frontend_source'] ?>/img/producer/icon/copy-link.svg"></div>
        </div>
    </div>
    <div class="product">
        <div class="img">
            <img src="<?= $params['frontend_source'] ?>/img/producer/card.png"/>
        </div>
        <div class="txt">
            <p class="t1">恒大海上威尼斯 邂逅那一片“双色海”</p>
            <p class="t2">酒店名称：启东 | 恒大海上威尼斯酒店</p>
            <p class="t2">分佣金额：7.9￥起</p>
            <div class="copy"><img src="<?= $params['frontend_source'] ?>/img/producer/icon/copy-link.svg"></div>
        </div>
    </div>
</div>