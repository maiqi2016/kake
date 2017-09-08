<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>


<header>
    个人中心
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>
<div class="body">
    
    <div class="blank"></div>

    <div class="kake-travel">
        <img class="photo" src="<?= current($producer['logo_preview_url']) ?>">
        <a class="txt" href="<?= Url::to(['producer/setting']) ?>">
            <p class="info"><?= $producer['name'] ?></p>
            <span class="phone-number"><?= $producer['phone'] ?></span>
        </a>
        <img class="arrow-01" src="<?= $params['frontend_source'] ?>/img/producer/icon/arrow-01.svg">
    </div>

    <div class="blank"></div>

    <div class="link-info">
        <div class="two-code common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/two-code.svg">
            <a href="<?= Url::to(['producer/qr-code']) ?>">我的二维码</a>
        </div>

        <div class="popularize-link common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/popularize-link.svg">
            <a href="<?= Url::to(['producer/link']) ?>">我的推广链接</a>
        </div>  
    </div>

    <div class="blank"></div>

    <div class="distribution">
        <div class="product-list common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/product-list.svg">
            <a href="<?= Url::to(['producer/product-list']) ?>">分销产品列表</a>
        </div>

        <div class="record common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/record.svg">
            <a href="<?= Url::to(['producer/order-list']) ?>">分销记录</a>
        </div>

        <div class="get-money common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/get-money.svg">
            <a href="<?= Url::to(['producer/withdraw']) ?>">分佣提现</a>
        </div>

    </div>

    <div class="blank"></div>
</div>
