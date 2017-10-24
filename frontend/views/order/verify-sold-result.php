<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<div class="result">
    <span>您已核销成功<span>
    <a href="#" class="b1 active">继续核销</a>
    <a href="#" class="b2">返回首页</a>
</div>