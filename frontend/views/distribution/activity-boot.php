<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>
<div class="header-two">
    <img src="<?= $params['frontend_source'] ?>/img/banner1.png">
    <div class="detail">
        <h1><?= $prize['name'] ?></h1>
        <p><?= $prize['title'] ?></p>
        <span>价值￥<?= $prize['sale_price'] ?></span>
        <div class="has-yes" kk-modal=".modal-one" data-width="90%" data-backdrop-close="static">立即抽奖</div>
        <div class="has-no">活动未开始</div>
    </div>
</div>
<div class="blank-two"></div>
<div class="description">
    <?= $prize['description'] ?>
    <a href="#"> >>点击查看套餐详情<< </a>
</div>
<div class="blank"></div>
<div class="hot-list">
    <a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/bichizizhu.gif"></a>
    <ul>
        <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/bichizizhu.gif"></a></li>
        <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/bichizizhu.gif"></a></li>
        <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/bichizizhu.gif"></a></li>
    </ul>
</div>
<div class="destination">
    <a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/bichizizhu.gif"></a>
    <ul class="clearfix">
        <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/banner1.png"></a></li>
        <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/banner1.png"></a></li>
        <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/banner1.png"></a></li>
        <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/banner1.png"></a></li>
    </ul>
</div>

<div class="qr-code">
    <a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/bichizizhu.gif"></a>
</div>

<div class="kk-modal">
    <div class="modal-one">
        <img data-dismiss="modal" src="<?= $params['frontend_source'] ?>/img/distribution/close-modal.png">
        <input type="text" ng-model="phone" placeholder="请输入手机号码" />
        <input type="text" ng-model="captcha" placeholder="请输入验证码" />
        <span kk-sms="{{phone}}" data-type=4>获取验证码</span>
        <div kk-tap="code(phone, captcha)">确定</div>
    </div>
</div>

<!-- $prize 变量中包含了奖品相关数据 -->
<!-- $channel 变量为分销商识别号 -->