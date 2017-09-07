<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<!-- <?= \common\components\Helper::dump($producer) ?> -->

<header>
    个人资料
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>
<div class="body">
    
    <div class="blank" style="margin-top:40px"></div>

    <div class="out">
        <div class="inner">
            <div class="photo">
                <span>头像</span>
                <img class="right" src="<?= current($producer['logo_preview_url']) ?>">
            </div>
            <div class="name">
                <span ng-init="name = '<?= $producer['name'] ?>'">名称</span>
                <input class="right" ng-model="name" placeholder="">
            </div>
            <div class="style">
                <div class="out" kk-tap="showWay = !showWay">
                    <span>收款账号类型</span>
                    <span class="right">支付宝</span>
                </div>
                <div class="style-2 kk-animate" ng-show="showWay" ng-class="{'kk-b2s-show': showWay}">
                    <span class="way">支付宝</span>
                    <span class="way">微信</span>
                </div>
            </div>
            
            <div class="number" ng-init="phone = '<?= $producer['phone'] ?>'">
                <span>收款账号</span>
                <input class="right" ng-model="phone" placeholder="">
            </div>
        </div>
    </div>

    <div class="blank"></div>

    <div class="save">保存</div>

</div>