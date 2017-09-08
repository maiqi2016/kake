<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<header>
    分销记录
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body">

    <div class="total">
        <div class="inner">
            <div class="left common">
                <p class="p1">￥<span>0.00</span></p>
                <p class="p2"><img src="<?= $params['frontend_source'] ?>/img/producer/icon/total-money.svg"><span>分销总金额</span></p>
            </div>
            <div class="line"></div>
            <div class="right common">
                <p class="p1"><span>0</span></p>
                <p class="p2"><img src="<?= $params['frontend_source'] ?>/img/producer/icon/total-number.svg"><span>分销总数量</span></p>
            </div>
        </div>
    </div>

    <div class="blank"></div>

    <div class="product-status">
        <div class="status">
            <img src="<?= $params['frontend_source'] ?>/img/producer/icon/no-pay.svg">
            <span>订单状态：未支付</span>
        </div>
        <div class="product">
            <div class="img">
                <img src="<?= $params['frontend_source'] ?>/img/producer/card.png"/>
            </div>
            <div class="txt">
                <p class="t1">童趣主题房 快乐亲子行</p>
                <p class="t2">购买者名称：喀客旅行</p>
                <p class="t2">酒店名称：</p>
                <p class="t2">分佣金额：</p>
            </div>
        </div>
        <div class="blank"></div>
    </div>

    <div class="product-status">
        <div class="status">
            <img src="<?= $params['frontend_source'] ?>/img/producer/icon/no-pay.svg">
            <span>订单状态：未支付</span>
        </div>
        <div class="product">
            <div class="img">
                <img src="<?= $params['frontend_source'] ?>/img/producer/card.png"/>
            </div>
            <div class="txt">
                <p class="t1">童趣主题房 快乐亲子行</p>
                <p class="t2">购买者名称：喀客旅行</p>
                <p class="t2">酒店名称：</p>
                <p class="t2">分佣金额：</p>
            </div>
        </div>
        <div class="blank"></div>
    </div>
    
    

</div>