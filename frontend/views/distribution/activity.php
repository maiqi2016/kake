<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div class="header">
    <img class="photo" src="<?= current($channelInfo['logo_preview_url']) ?>">
    <div class="txt"><?= $channelInfo['name'] ?></div>
</div>
<div class="lottery-draw">
    <div class="pro">
        <p>开奖人数【 <span class="some">65</span>/<span class="all">100 </span>】</p>
        <div class="progress">
          <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
            <span class="sr-only">40% Complete (success)</span>
          </div>
        </div>
        <span class="percent">65%</span>
    </div>
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
<div class="rule"><img src="<?= $params['frontend_source'] ?>/img/distribution/rule.jpg"></div>

<div class="share" ng-show="showShare" kk-tap="showShare=!showShare">
    <img data-dismiss="modal" src="<?= $params['frontend_source'] ?>/img/distribution/share.png">
</div>

<!-- $channel 变量为分销商识别号 -->
<!-- $prize 变量中包含了奖品相关数据 -->
<!-- $code 变量中包含了抽奖码列表 -->
<!-- $channelInfo 分销商信息 -->