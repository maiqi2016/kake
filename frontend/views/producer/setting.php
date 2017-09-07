<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<header>
    个人资料
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>
<div class="body">

    <div class="blank" style="margin-top:40px"></div>

    <div class="out" ng-init='setting = <?= json_encode($angular) ?>'>
        <div class="inner" kk-ajax-upload="div.photo" data-action="producer/upload-avatar-crop" data-callback="handleUpload">
            <div class="photo">
                <span>头像</span>
                <img class="right" src="<?= current($producer['logo_preview_url']) ?>">
            </div>
            <div class="name">
                <span>名称</span>
                <input class="right" ng-model="setting.name">
            </div>
            <div class="style">
                <div class="out" kk-tap="showWay = !showWay">
                    <span>收款账号类型</span>
                    <span class="right">{{payment_method[setting.account_type]}}</span>
                </div>
                <div class="style-2 kk-animate" ng-show="showWay" ng-class="{'kk-b2s-show': showWay}">
                    <span class="way" ng-repeat="(i, name) in payment_method" kk-repeat-done="radio" data-key="{{i}}" ng-class="{'active': i == setting.account_type}">{{name}}</span>
                </div>
            </div>

            <div class="number">
                <span>收款账号</span>
                <input class="right" ng-model="setting.account_number">
            </div>
        </div>
    </div>

    <div class="blank"></div>

    <div class="save" kk-tap="editSetting()">保存</div>

</div>