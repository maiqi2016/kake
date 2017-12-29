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
    <div class="pro">
        <p>开奖人数 (<span class="some"><?= $total ?></span>/<span class="all"><?= $prize['standard_code_number'] ?></span>) </p>
        <div class="progress">
          <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" style="width: <?= $percent ?>%">
          </div>
        </div>
        <span class="percent"> <?= $percent ?>%</span>
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
