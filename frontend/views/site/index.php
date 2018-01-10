<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'site';
?>

<div class="body">
    <!-- Banner -->

    <div class="menu-box" kk-menu-lm>
        <div class="menu">
            <img src="<?= $params['frontend_source'] ?>/img/menu.svg"/>
        </div>
    </div>
    <?php if (!empty($focusList)): ?>
        <ul class="focus-point">
            <?php foreach ($focusList as $focus): ?>
                <li></li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>

    <?php if (!empty($focusList)): ?>
        <div class="carousel index-banner-height" id="focus-hot" kk-focus=".focus-point" data-point-current="on"
             data-stayTime="10000" style="overflow:hidden">

            <div class="carousel-scroller product-focus">
                <?php $key = 0;foreach ($focusList as $focus): ?>
                    <?php
                    $event = ($key == 0) ? 'kk-tap="showCalFn()" ' : null;
                    $key++;
                    $ad = !empty($focus['preview_url']);
                    $url = $ad ? $focus['link_url'] : Url::toRoute([
                        'detail/index',
                        'id' => $focus['id']
                    ]);
                    $target = $ad ? $focus['target_info'] : '_self';
                    $img = $ad ? current($focus['preview_url']) : current($focus['cover_preview_url']);
                    ?>

                    <a href="<?= $url ?>" _target="<?= $target ?>" <?= $event ?>>
                        <img src="<?= $img ?>"/>
                    </a>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif; ?>
    <p class="animated" ng-show="showCal" ng-class="{'flipInY': showCal}" kk-activity-cal></p>
    <!-- Hot-aim -->
    <div class="kake-box hot-aim" ng-init="initEffect()">
        <div class="kake-title">
            <h3 kk-link="<?= Url::toRoute(['items/region']) ?>">
                <img src="<?= $params['frontend_source'] ?>/img/index-icon-aim.svg"/>
                热门目的地
            </h3>
            <a href="<?= Url::toRoute(['items/region']) ?>">更多<img
                        src="<?= $params['frontend_source'] ?>/img/index-icon-more.svg"/></a>
        </div>
        <div class="carousel kake-theme" id="carousel-scroller-aim" kk-scroll data-callback-change="effect">
            <div class="carousel-scroller scroll">
                <?php foreach ($plateList as $i => $item): ?>
                    <div>
                        <a href="<?= Url::toRoute([
                            'items/index',
                            'plate' => $item['id']
                        ]) ?>">
                            <img src="<?= current($item['preview_url']) ?>"/>
                        </a>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>

    <!-- Flash-shopping-zone -->
    <div class="kake-box flash-shopping-zone">
        <div class="kake-title">
            <h3 kk-link="<?= Url::toRoute(['items/index']) ?>">
                <img src="<?= $params['frontend_source'] ?>/img/index-icon-sales.svg"/>
                闪购专区
            </h3>
            <a href="<?= Url::toRoute(['items/index']) ?>">更多<img
                        src="<?= $params['frontend_source'] ?>/img/index-icon-more.svg"/></a>
        </div>
        <div class="carousel kake-theme" id="carousel-scroller-flash" kk-focus-camel>
            <div class="carousel-scroller scroll">
                <div class="product_image"></div>
                <?php foreach ($flashSalesList as $flashSales): ?>
                    <div class="product_image">
                        <a href="<?= Url::toRoute([
                            'detail/index',
                            'id' => $flashSales['id']
                        ]) ?>">
                            <img class="img-responsive" src="<?= current($flashSales['cover_preview_url']) ?>"/>
                        </a>
                        <p><b>￥<?= $flashSales['min_price'] ?></b><span><?= $flashSales['title'] ?></span></p>
                    </div>
                <?php endforeach ?>
                <div class="product_image"></div>
            </div>
        </div>
    </div>

    <!-- Activity -->
    <div class="kake-box activity">
        <?php if (!empty($bannerList)): ?>
            <div class="carousel" id="carousel-scroller-activity" kk-focus style="overflow:hidden;">
                <div class="carousel-scroller activity">
                    <?php foreach ($bannerList as $item): ?>
                        <a href="<?= $item['url'] ?>" _target="<?= $item['target_info'] ?>">
                            <img src="<?= current($item['preview_url']) ?>"/>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recommend-item -->
    <div class="kake-box product">
        <div class="title">
            <img src="<?= $params['frontend_source'] ?>/img/index-icon-recommand.png"/>
        </div>
        <div class="list">
            <ul>
                <?php if (!empty($standardHtml)): ?>
                    <?= $standardHtml ?>
                <?php endif; ?>
            </ul>
            <a href="<?= Url::toRoute(['items/index']) ?>" class="more" kk-pull-up="100">查看更多</a>
        </div>
    </div>
</div>
