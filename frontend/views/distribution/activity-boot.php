<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div style="text-align: center; margin-top: 18%">
    <h1>麻瓜梦</h1>
    <p class="help-block">惊不惊喜，开不开心</p>
</div>

<!-- $prize 变量中包含了奖品相关数据 -->
<!-- $channel 变量为分销商识别号 -->