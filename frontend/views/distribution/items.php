<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div class="opening" ng-show="showAnimate" ng-init="autoHide()" kk-tap="hidden()">
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

<div ng-show="showBody">
    <div class="header" ng-init='upstream = <?= json_encode($upstream, JSON_UNESCAPED_UNICODE) ?>'>
        <div class="inner">
            <form class="search" id="box" action="/">
                <input type="hidden" name="r" value="items/index">
                <input id="search-info" type="search" name="keyword" ng-model="search" placeholder="恒大海上威尼斯酒店">
                <ul ng-show="search">
                    <a href="<?= Url::to(['items/index', 'upstream' => '']) ?>{{item.id}}" ng-repeat="item in upstream | filter:search">
                        <li>{{item.name}}</li>
                    </a>
                </ul>
            </form>
            <div class="menu" kk-menu-lm><img src="<?= $params['frontend_source'] ?>/img/menu.svg"></div>
        </div>
        <div class="out kk-animate" ng-show="showTab" ng-class="{'kk-show': showTab}">
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
                        <a href="<?= Url::to(['items/index', 'region' => $id]) ?>"><li><?= $name ?></li></a>
                    <?php endforeach ?>
                </ul>
                <?php endforeach ?>
            </div>
        </div>

        <div id="focus-card" class="focus-card" kk-focus-card>
            <div></div>
            <ul class="card-carousel">
                <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/1.png"></a></li>
                <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/2.png"></a></li>
                <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/3.png"></a></li>
                <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/4.png"></a></li>
            </ul>
        </div>
    </div>   

    <div class="body">
        <div class="nav" kk-fixed="window.screen.height">
            <ul kk-anchor="active" data-element="li">
                <li data-anchor=".needHotel"><a href="#" class="hotel"><span>精品酒店</span></a></li>
                <li data-anchor=".needEat"><a href="#" class="eat"><span>自助餐</span></a></li>
                <li data-anchor=".needPlay"><a href="#" class="play"><span>亲子玩乐</span></a></li>
            </ul>
        </div>
        <div class="blank"></div>

        <div class="product-one clearfix">
            <div class="product-detail">
                <ul class="cleafix">
                <?php foreach ($top as $item): ?>
                    <li>
                        <a href="<?= Url::to(['detail/index', 'id' => $item['id']]) ?>">
                            <div class="photo">
                                <img src="<?= current($item['cover_preview_url']) ?>">
                                <span class="price">￥<?= $item['min_price'] ?></span>
                                <p><?= $item['name'] ?></p>
                            </div>
                        </a> 
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="needHotel same">
            <a href="#" class="bannerHotel">
                <img src="<?= $params['frontend_source'] ?>/img/distribution/bizhujiudian.gif">
            </a>
        </div>
        <ul class="product-two clearfix" kk-ajax-load="distribution/ajax-items" data-over="<?= $over ?>" data-params="uid=<?= $uid ?>">
            <?= trim($html) ?>
        </ul>

        <div class="needEat same">
            <a href="#" class="bannerHotel">
                <img src="<?= $params['frontend_source'] ?>/img/distribution/bichizizhu.gif">
            </a>
        </div>
        <ul class="product-two clearfix" kk-ajax-load="distribution/ajax-items" data-over="<?= $over ?>" data-params="uid=<?= $uid ?>">
            <?= trim($html) ?>
        </ul>

        <div class="needPlay same">
            <a href="#" class="bannerHotel">
                <img src="<?= $params['frontend_source'] ?>/img/distribution/bixuanwanle.gif">
            </a>
        </div>
        <ul class="product-two clearfix" kk-ajax-load="distribution/ajax-items" data-over="<?= $over ?>" data-params="uid=<?= $uid ?>">
            <?= trim($html) ?>
        </ul>
    </div>
</div>




