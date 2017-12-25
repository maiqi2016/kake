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
    <div class="up">
        <span class="txt">我的抽奖码为</span><span class="invite">点击右上角邀请好友抽奖</span>
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

<!-- $channel 变量为分销商识别号 -->
<!-- $prize 变量中包含了奖品相关数据 -->
<!-- $code 变量中包含了抽奖码列表 -->
<!-- $channelInfo 分销商信息 -->