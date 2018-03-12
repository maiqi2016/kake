<?php
/* @var $this yii\web\View */

use Oil\src\Helper;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<header>
    分销记录
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body">

    <div class="total">
        <div class="inner">
            <div class="left common">
                <p class="p1">￥<span><?= $quota ?></span></p>
                <p class="p2"><img
                            src="<?= $params['frontend_source'] ?>/img/producer/icon/total-money.svg"><span>分销总金额</span>
                </p>
            </div>
            <div class="line"></div>
            <div class="right common">
                <p class="p1"><span><?= $total ?></span></p>
                <p class="p2"><img src="<?= $params['frontend_source'] ?>/img/producer/icon/total-number.svg"><span>分销总数量</span>
                </p>
            </div>
        </div>
    </div>

    <div class="blank"></div>

    <?php foreach ($list as $item): ?>
        <div class="product-status">
            <div class="status">
                <img src="<?= $params['frontend_source'] ?>/img/producer/icon/payment_<?= $item['payment_state'] ?>.svg">
                <span>订单状态：<?= $item['payment_state_info'] ?></span>
            </div>
            <div class="product">
                <div class="img">
                    <img src="<?= current($item['cover_preview_url']) ?>"/>
                </div>
                <div class="txt">
                    <p class="t1"><?= $item['title'] ?></p>
                    <p class="t2">购买粉丝：<?= $item['buyer_name'] ?></p>
                    <p class="t2">酒店名称：<?= $item['name'] ?></p>
                    <p class="t2">套餐状态：<?= $item['description'] ?></p>
                    <?php if ($item['payment_state']): ?>
                        <p class="t2">已达分佣：<?= Helper::money($item['commission_quota']) ?></p>
                        <p class="t2">未达分佣：<?= Helper::money($item['commission_quota_out']) ?></p>
                    <?php else: ?>
                        <p class="t2">预计分佣：<?= Helper::money($item['commission_quota_out']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="blank"></div>
        </div>
    <?php endforeach; ?>

    <div class="settlement" kk-tap="settlement()">
        <img src="<?= $params['frontend_source'] ?>/img/producer/icon/settlement.svg">
        <p>结算</p>
    </div>
</div>