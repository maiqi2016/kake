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
        <?php if (strtotime(date('Y-m-d 00:00:00')) < strtotime($prize['from'])): ?>
        <div class="has-no">活动未开始</div>
        <?php else: ?>
        <div class="has-yes" kk-modal=".modal-one" data-width="90%" data-backdrop-close="static">立即抽奖</div>
        <?php endif; ?>
    </div>
</div>
<div class="blank-two"></div>
<div class="description">
    <?= $prize['description'] ?>
</div>
<a class="detail" href="<?= $prize['link_url'] ?>"> >>点击查看套餐详情<< </a>
<div class="blank"></div>
<div class="hot-list">
    <a href="<?= Url::toRoute(['items/index']) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/hot1.png"></a>
    <ul>
        <li><a href="<?= Url::toRoute(['items/index', 'classify' => 0]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/hotel.png"></a></li>
        <li><a href="<?= Url::toRoute(['items/index', 'classify' => 1]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/eat.png"></a></li>
        <li><a href="<?= Url::toRoute(['items/index', 'classify' => 2]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/play.png"></a></li>
    </ul>
</div>
<div class="destination">
    <a href="<?= Url::toRoute(['items/region']) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/hot2.png"></a>
    <ul class="clearfix">
        <li><a href="<?= Url::toRoute(['items/index','plate' => 3]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/1.jpg"></a></li>
        <li><a href="<?= Url::toRoute(['items/index','plate' => 4]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/2.jpg"></a></li>
        <li><a href="<?= Url::toRoute(['items/index','plate' => 9]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/3.jpg"></a></li>
        <li><a href="<?= Url::toRoute(['items/index','plate' => 5]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/4.jpg"></a></li>
    </ul>
</div>

<div class="qr-code">
    <a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/qr.png"></a>
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