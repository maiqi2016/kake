<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div class="header" ng-init='hotel = <?= json_encode($hotel, JSON_UNESCAPED_UNICODE) ?>'>
    <div class="inner">
        <div class="area" ng-init="area = true" kk-tap="toggle()">筛选</div>
        <div class="search" id="box">
            <input id="search-info" type="text" name="keyword" ng-model="search" placeholder="关键字">
            <ul ng-show="search">
                <a href="<?= Url::to(['items/index', 'hotel' => '']) ?>{{item.id}}" ng-repeat="item in hotel | filter:search">
                    <li>{{item.name}}</li>
                </a>
            </ul>
        </div>
        <div class="menu" kk-menu="#menu"><img src="<?= $params['frontend_source'] ?>/img/menu.svg"></div>
    </div>
    <div class="select-area hidden" ng-toggle="areaSelect">
        <ul class="left" kk-tab-card="active" data-element="li">
            <?php
            $index = 0;
            foreach ($region as $plate => $regions):
                $cls = !$index ? 'class="active"' : '';
                $index++;
            ?>
                <li <?= $cls ?> data-card=".region_<?= $index ?>"><?= $plate ?></li>
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
    <div class="personal">
        <div class="photo">
            <img class="photo" src="<?= current($producer['logo_preview_url']) ?>">
        </div>
        <p class="info"><?= $producer['name'] ?></p>
    </div>
</div>

<div class="body">
    <div class="product-one">
        <div class="line"></div>
        <div class="title">产品列表</div>
        <div class="product-detail">
            <ul class="clearfix"> 
            <?php foreach ($top as $item): ?>
                <li>
                    <a href="<?= Url::to(['detail/index', 'id' => $item['id']]) ?>">
                        <div class="photo">
                            <img src="<?= current($item['cover_preview_url']) ?>">
                            <div class="price"><span class="s">￥</span><span class="b"><?= $item['min_price'] ?></span></div>
                            <p><?= $item['name'] ?></p>
                        </div>
                    </a> 
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <ul class="product-two" kk-ajax-load="distribution/ajax-items" data-over="<?= $over ?>" data-params="uid=<?= $uid ?>">
        <?= trim($html) ?>
    </ul>
</div>

