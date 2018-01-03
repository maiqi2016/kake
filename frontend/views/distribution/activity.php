<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div class="header">
    <img class="photo" src="<?= $user->head_img_url ?>">
    <div class="txt"><?= $user->username ?></div>
</div>
<div class="lottery-draw">
    <div class="pro" ng-init='hide();share();'>
        <p>开奖人数 (<span class="some"><?= $total ?></span>/<span class="all"><?= $prize['standard_code_number'] ?></span>) </p>
        <div class="progress">
          <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" style="width: <?= $percent>100?100:$percent ?>%">
          </div>
        </div>
        <span class="percent"> <?= $percent ?>%</span>
        <?php if ($percent <= 60): ?>
            <img class="call" src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/call.png">
        <?php elseif ($percent <= 90): ?>
            <img class="little" src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/little.png">
        <?php else: ?>
            <img class="goon" src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/goon.png">
        <?php endif; ?>
    </div>

    <!-- 麻瓜梦 -->
    <p>本次活动中奖码：<?= !empty($prize['win_code']) ? $prize['win_code'] : '待开奖' ?></p>

    <div class="up">
        <span class="txt">我的抽奖码为</span><span class="invite" kk-tap="showShare=!showShare">邀请好友抽奖</span>
    </div>
    <div class="down">
        <?php foreach ($code as $c => $user): ?>
            <span class="lottery"><?= $c ?></span>
            <span class="date"><?= $user ?: date('Y-m-d') ?></span><br>
        <?php endforeach; ?>

    </div>

</div>
<div class="blank"></div>
<div class="description">
    <?= $prize['description'] ?>
</div>
<a class="detail" href="<?= $prize['link_url'] ?>"> >>点击查看奖品详情<< </a>
<div class="rule"><img src="<?= $params['frontend_source'] ?>/img/distribution/rule.jpg"></div>

<div class="share" ng-show="showShare" kk-tap="showShare=!showShare">
    <img src="<?= $params['frontend_source'] ?>/img/distribution/share.png">
</div>

<div class="blank"></div>
<div class="hot-list">
    <a href="<?= Url::toRoute(['items/index']) ?>"><img
                src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/hot1.png"></a>
    <ul>
        <li><a href="<?= Url::toRoute([
                'items/index',
                'classify' => 0
            ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/hotel.png"></a></li>
        <li><a href="<?= Url::toRoute([
                'items/index',
                'classify' => 1
            ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/eat.png"></a></li>
        <li><a href="<?= Url::toRoute([
                'items/index',
                'classify' => 2
            ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/play.png"></a></li>
    </ul>
</div>
<div class="destination">
    <a href="<?= Url::toRoute(['items/region']) ?>"><img
                src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/hot2.png"></a>
    <ul class="clearfix">
        <li><a href="<?= Url::toRoute([
                'items/index',
                'plate' => 3
            ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/1.jpg"></a></li>
        <li><a href="<?= Url::toRoute([
                'items/index',
                'plate' => 4
            ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/2.jpg"></a></li>
        <li><a href="<?= Url::toRoute([
                'items/index',
                'plate' => 9
            ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/3.jpg"></a></li>
        <li><a href="<?= Url::toRoute([
                'items/index',
                'plate' => 5
            ]) ?>"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/4.jpg"></a></li>
    </ul>
</div>

<div class="qr-code">
    <a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/activity-boot/qr.png"></a>
</div>

<script>var d='<?php echo $prize['to'];?>';</script>
