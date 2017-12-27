<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>
<?php if ($animate): ?>
    <i ng-init="showBody=false; ">
    <div class="opening" ng-show="showAnimate" ng-init="autoHide(7000)" kk-tap="hidden()">
        <div class="opening_bj"></div>
        <div class="small_bj">
            <img src="<?= $params['frontend_source'] ?>/img/opening/small_bj.png" class="small_bg">
        </div>
        <div class="hang">
            <img src="<?= $params['frontend_source'] ?>/img/opening/hang.png">
        </div>
        <img class="photo" src="<?= current($producer['logo_preview_url']) ?>">
        <div class="guangquan">
            <img src="<?= $params['frontend_source'] ?>/img/opening/guangquan2.png">
        </div>

        <div kk-print-text="<?= $producer['name'] ?>" class="txt"></div>
    </div>
<?php endif; ?>


<div ng-show="showBody" class="shape-fixed">
    <div class="header">
        <div class="carousel kake-theme" id="carousel-scroller-luck" kk-focus-camel>
            <div class="carousel-scroller scroll">
                <div class="product_image"></div>
                <?php foreach ($focusList as $item): ?>
                    <div class="product_image">
                        <a href="<?= $item['link_url'] ?>">
                            <img class="img-responsive" src="<?= current($item['preview_url']) ?>"/>
                        </a>
                    </div>
                <?php endforeach ?>
                <div class="product_image"></div>
            </div>
        </div>
    </div>
    <img class="luck-draw-img animated" ng-class="{'zoomIn': shake}" kk-tap="showCal = !showCal" src="<?= $params['frontend_source'] ?>/img/distribution/choujiang.png">
    <div class="luck-draw animated" ng-show="showCal" ng-class="{'flipInY': showCal}" ng-init='days = <?= json_encode($days)?>;cal();cls();'>
        <span><?= date('m') ?>月</span>
        <img src="<?= $params['frontend_source'] ?>/img/distribution/month-border.png">
        <div class="date">
            <ul class="clearfix list" ng-bind-html="li"></ul>
        </div>
    </div>

    <div class="body">
        <div class="nav">
            <ul kk-anchor="active" data-element="li">
                <?php foreach ($classify as $key => $name): ?>
                    <li data-anchor=".classify_anchor_<?= $key ?>"><a href="javascript:void(0)" class="classify_<?= $key ?>"><span><?= $name ?></span></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="product-one">
            <div class="product-detail">
            <ul class="clearfix">
                <?= trim($top) ? $top : '<div class="no-data">暂无相关产品</div>' ?>
            </ul>
        </div>

        <?php if (!empty(trim($html_0))): ?>
        <div class="needHotel same classify_anchor_0">
            <?php if (!empty($bannerList[0])): ?>
                <a href="<?= $bannerList[0]['link_url'] ?>" class="bannerHotel">
                    <img src="<?= current($bannerList[0]['preview_url']) ?>">
                </a>
            <?php endif; ?>
                </ul>
            </div>
        </div>
        <ul class="product-two clearfix">
            <?= trim($html_0) ? $html_0 : '<div class="no-data">暂无相关产品</div>' ?>
        </ul>
        <?php endif; ?>

        <?php if (!empty(trim($html_1))): ?>
        <div class="needEat same classify_anchor_1">
            <?php if (!empty($bannerList[1])): ?>
                <a href="<?= $bannerList[1]['link_url'] ?>" class="bannerHotel">
                    <img src="<?= current($bannerList[1]['preview_url']) ?>">
                </a>
            <?php endif; ?>
        </div>
        <ul class="product-two clearfix">
            <?= trim($html_1) ? $html_1 : '<div class="no-data">暂无相关产品</div>' ?>
        </ul>
        <?php endif; ?>

        <?php if (!empty(trim($html_2))): ?>
        <div class="needPlay same classify_anchor_2">
            <?php if (!empty($bannerList[2])): ?>
                <a href="<?= $bannerList[2]['link_url'] ?>" class="bannerHotel">
                    <img src="<?= current($bannerList[2]['preview_url']) ?>">
                </a>
            <?php endif; ?>
        </div>
        <ul class="product-two clearfix">
            <?= trim($html_2) ? $html_2 : '<div class="no-data">暂无相关产品</div>' ?>
        </ul>
        <?php endif; ?>

        <footer>
            <div class="menu" kk-menu-lm><img src="<?= $params['frontend_source'] ?>/img/menu.svg"></div>
        </footer>
    </div>
</div>