<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'detail';
?>

<div class="body" ng-init="service.goToTop('.back-top');run()">
    <div class="banner">
        <div class="menu-box" kk-menu-lm data-pos-x="-15" data-pos-y="-15">
            <div class="menu">
                <img src="<?= $params['frontend_source'] ?>/img/menu.svg"/>
            </div>
        </div>
        <div class="carousel" id="focus-hot" kk-focus=".focus-number"
             data-number-tpl="< {NOW} / {TOTAL} > Sold: <?= $detail['max_sales'] ?>"
             style="overflow: hidden">
            <div class="carousel-scroller product-focus">
                <?php if (!empty($detail['slave_preview_url'])): ?>
                    <?php foreach ($detail['slave_preview_url'] as $photo): ?>
                        <img src="<?= $photo ?>"/>
                    <?php endforeach ?>
                <?php endif; ?>
            </div>
        </div>
        <span class="focus-number"></span>
    </div>
    <div class="hotel-detail">
        <div class="hotel-detail-1">
            <?= $detail['product_upstream_name'] ?>
        </div>
        <div class="hotel-detail-2">
            <span class="hotel-detail-2-1"><?= $detail['title'] ?></span>
            <span class="hotel-detail-2-1 hidden">已售 <span><?= $detail['max_sales'] ?></span> 份</span>
        </div>
    </div>
    <div class="classify">
        <div class="classify-1" kk-anchor="cur-1" data-element="div">
            <div class="classify-1-1 cur-1" data-anchor=".card_first">
                <span>详情介绍</span>
            </div>
            <div class="thinner-border"></div>
            <div class="classify-1-2" data-anchor=".card_second">
                <span>预订须知</span>
            </div>
        </div>
    </div>
    <div class="detail-hotel_1 card_first">
        <div class="detail-hotel">
            <div class="detail-hotel-1">
                费用包含
            </div>
            <div class="detail-hotel-2">
                <?= $detail['cost'] ?>
            </div>
        </div>
        <div class="detail-hotel">
            <div class="detail-hotel-2 detail-hotel_2">
                <?= $detail['recommend'] ?>
            </div>
        </div>
    </div>
    <div class="notice card_second">
        <div class="detail-hotel">
            <div class="detail-hotel-1">
                使用说明
            </div>
            <div class="notice-money bor">
                <?= $detail['use'] ?>
            </div>
        </div>
        <div class="detail-hotel">
            <div class="detail-hotel-1">
                退改规则
            </div>
            <div class="notice-money">
                <?= $detail['back'] ?>
            </div>
        </div>
    </div>

    <div class="qr-code">
        <a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/qr.png"></a>
    </div>

    <div class="hot-list">
        <ul>
            <li><a href="<?= Url::toRoute([
                    'items/index',
                    'classify' => 0
                ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/hotel.png"></a></li>
            <li><a href="<?= Url::toRoute([
                    'items/index',
                    'classify' => 1
                ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/eat.png"></a></li>
            <li><a href="<?= Url::toRoute([
                    'items/index',
                    'classify' => 2
                ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/play.png"></a></li>
        </ul>
    </div>

    <footer>
        <?php
        $night = empty($detail['night_times']) ? '' : " / {$detail['night_times']}晚";
        ?>
        <div class="buy">
            <?php $url = empty($detail['sell_out']) ? Url::toRoute([
                'detail/choose-package',
                'id' => $detail['id']
            ]) : 'javascript:void(null)' ?>
            <a href="<?= $url ?>">
                <p>￥<span><?= $detail['min_price'] ?></span></p>
                <?php if (empty($detail['sell_out'])): ?>
                    预订
                <?php else: ?>
                    已售罄
                <?php endif; ?>
            </a>
        </div>

        <div class=" service">
            <a href="tel:<?= Yii::$app->params['company_tel'] ?>">
                <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/custom.svg"/>
            </a>
        </div>
        <div class="back-top">
            <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/back-top.svg"/>
        </div>
    </footer>
</div>