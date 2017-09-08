<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'producer';
?>

<header>
    佣金提现页面
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body">
    <div class="blank" style="margin-top:40px"></div>

    <div class="rest">
        <div class="rest-money common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/rest-money.svg">
            <p class="txt">佣金余额（元）</p>
        </div>
        <div class="money"><?= $quota ?></div>
    </div>

    <div class="blank"></div>

    <div class="withdraw" ng-init="money.quota = <?= $quota ?>">
        <div class="account1 common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/acount.svg">
            <p class="txt">提现账号</p>
            <span class="right"><?= $producer['account_number'] ?> (<?= $producer['account_type_info'] ?>)</span>
        </div>
        <div class="account2 common">
            <img class="img-style" src="<?= $params['frontend_source'] ?>/img/producer/icon/withdraw-money.svg">
            <p class="p1">提现余额</p>
            <div class="txt2">
                <span class="s1">￥</span>
                <input type="number" ng-model="money.withdraw" class="s2" style="border:none;">
            </div>
        </div>
        <div class="account3 common" kk-tap="withdrawAll()">
            <span class="right">全部提取</span>
        </div>
    </div>

    <div class="blank"></div>

    <div class="save" kk-tap="withdraw()">确认提取</div>
    
</div>