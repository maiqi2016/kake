<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>
<?php if (!$animate): ?>
    <i ng-init="showAnimate=false; autoHide(0)">
<?php endif; ?>
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

<div ng-show="showBody" class="shape-fixed">
    <div class="header" ng-init='upstream = <?= json_encode($upstream, JSON_UNESCAPED_UNICODE) ?>'>
        <div class="inner">
            <form class="search" id="box" action="/">
                <input type="hidden" name="r" value="items/index">
                <input id="search-info" type="search" name="keyword" ng-model="search" placeholder="酒店名称">
                <ul ng-show="search">
                    <a href="<?= Url::toRoute([
                        'items/index',
                        'upstream' => ''
                    ]) ?>{{item.id}}" ng-repeat="item in upstream | filter:search">
                        <li>{{item.name}}</li>
                    </a>
                </ul>
            </form>
            <div class="menu" kk-menu-lm><img src="<?= $params['frontend_source'] ?>/img/menu.svg"></div>
        </div>
        <div class="out kk-animate hidden" ng-show="showTab" ng-class="{'kk-show': showTab}">
            <div class="select-area">
                <ul class="left" kk-tab-card="active" data-element="li">
                    <?php
                    $index = 0;
                    foreach ($region as $plate => $regions):
                        $index++;
                        ?>
                        <li data-card=".region_<?= $index ?>"><?= $plate ?></li>
                    <?php endforeach ?>
                </ul>
                <?php
                $index = 0;
                foreach ($region as $items):
                    $index++;
                    ?>
                    <ul class="right region_<?= $index ?>">
                        <?php foreach ($items as $id => $name): ?>
                            <a href="<?= Url::toRoute([
                                'items/index',
                                'region' => $id
                            ]) ?>">
                                <li><?= $name ?></li>
                            </a>
                        <?php endforeach ?>
                    </ul>
                <?php endforeach ?>
            </div>
        </div>

        <div id="focus-card" class="focus-card" kk-focus-card>
            <div></div>
            <ul class="card-carousel">
                <?php foreach ($focusList as $item): ?>
                    <li><a href="<?= $item['link_url'] ?>"><img src="<?= current($item['preview_url']) ?>"></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="body">
        <div class="nav" kk-fixed="window.screen.height">
            <ul kk-anchor="active" data-element="li">
                <?php foreach ($classify as $key => $name): ?>
                    <li data-anchor=".classify_anchor_<?= $key ?>"><a href="javascript:void(0)" class="classify_<?= $key ?>"><span><?= $name ?></span></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="blank"></div>
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
        <div class="blank"></div>
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
        <div class="blank"></div>
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
        <div class="blank"></div>
        <ul class="product-two clearfix">
            <?= trim($html_2) ? $html_2 : '<div class="no-data">暂无相关产品</div>' ?>
        </ul>
        <?php endif; ?>

        <footer>
            <a href="<?= Url::toRoute(['order/index']) ?>">
                <img src="<?= $params['frontend_source'] ?>/img/producer/order.png">
            </a>
        </footer>
    </div>
</div>