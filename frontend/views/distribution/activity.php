<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div style="text-align: center; margin-top: 18%">
    <h1>麻瓜梦2</h1>
    <p class="help-block">是不是依然很惊喜</p>
</div>

<!-- $channel 变量为分销商识别号 -->
<!-- $prize 变量中包含了奖品相关数据 -->
<!-- $code 变量中包含了抽奖码列表 -->
<!-- $channelInfo 分销商信息 -->