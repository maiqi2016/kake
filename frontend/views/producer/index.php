<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<header>
    个人中心
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>
<div class="body">
    
    <div class="blank"></div>

    <a class="kake-travel" href="<?= Url::toRoute(['producer/setting']) ?>">
        <img class="photo" src="<?= current($producer['logo_preview_url']) ?>">
        <div class="txt">
            <p class="info"><?= $producer['name'] ?></p>
            <span class="phone-number"><?= $producer['phone'] ?></span>
        </div>
        <img class="arrow-01" src="<?= $params['frontend_source'] ?>/img/producer/icon/arrow-01.svg">
    </a>


    <div class="blank"></div>

    <div class="link-info">
        <a class="two-code common" href="<?= Url::toRoute(['producer/qr-code']) ?>">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/two-code.svg">
            我的二维码
        </a>

        <a class="popularize-link common" href="<?= Url::toRoute(['producer/link']) ?>">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/popularize-link.svg">
            我的推广链接
        </a>
    </div>

    <div class="blank"></div>

    <div class="distribution">
        <a class="product-list common" href="<?= Url::toRoute(['producer/product-list']) ?>">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/product-list.svg">
            分销产品列表
        </a>

        <a class="record common" href="<?= Url::toRoute(['producer/order-list']) ?>">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/record.svg">
            分销记录
        </a>

        <a class="get-money common" href="<?= Url::toRoute(['producer/withdraw']) ?>">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/get-money.svg">
            分佣提现
        </a>

    </div>

    <div class="blank"></div>
</div>
