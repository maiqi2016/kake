<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<div class="code"><?= $msg ?></div>

<button class="close">关闭</button>