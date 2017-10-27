<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<header>
    个人资料
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>
<div class="body">

    <div class="blank"></div>

    <div class="out" ng-init='setting = <?= json_encode($angular) ?>'>
        <div class="photo">
            <span>头像</span>
            <img class="right" src="<?= current($producer['logo_preview_url']) ?>">
        </div>
        <div class="inner" kk-ajax-upload="div.photo" data-action="producer/upload-avatar-crop" data-callback="handleUpload">
            <div class="name">
                <span>名称</span>
                <input class="right" ng-model="setting.name" placeholder="名称32个字符内">
            </div>
            <div class="style">
                <div class="out" kk-tap="show_way = !show_way">
                    <span>收款账号类型</span>
                    <span class="right">{{payment_method[setting.account_type] || '请选择账号类型'}}</span>
                </div>
                <div class="style-2 kk-animate" ng-show="show_way" ng-class="{'kk-b2s-show': show_way}">
                    <span class="way" ng-repeat="(i, name) in payment_method" kk-repeat-done="radio" data-key="{{i}}" ng-class="{'active': i == setting.account_type}">{{name}}</span>
                </div>
            </div>

            <div class="number kk-animate" ng-show="setting.account_type != 0" ng-class="{'kk-b2s-show': setting.account_type != 0}">
                <span>收款账号</span>
                <input class="right" ng-model="setting.account_number" placeholder="收款账号">
            </div>
        </div>
    </div>

    <div class="blank"></div>

    <div class="save" kk-tap="editSetting()">保存</div>

</div>