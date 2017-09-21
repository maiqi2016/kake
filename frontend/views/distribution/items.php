<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<?php if (!empty($producer)): ?>
    <div class="opening" >
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
<<<<<<< Updated upstream

        <div class="out-q" ng-init="showBody = false; autoHide()" kk-tap="hidden()">
            <div class="in-q"></div>
        </div>


=======
>>>>>>> Stashed changes
    </div>
<?php endif; ?>

<div ng-show="showBody">
    <div class="header" ng-init='hotel = <?= json_encode($hotel, JSON_UNESCAPED_UNICODE) ?>'>
        <div class="inner">
            <div class="area" kk-tap="toggle()">筛选</div>
            <form class="search" id="box" action="/">
                <input type="hidden" name="r" value="items/index">
                <input id="search-info" type="search" name="keyword" ng-model="search" placeholder="关键字">
                <ul ng-show="search">
                    <a href="<?= Url::to(['items/index', 'hotel' => '']) ?>{{item.id}}" ng-repeat="item in hotel | filter:search">
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
                                <span class="price"><i>￥</i><?= $item['min_price'] ?></span>
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
</div>




