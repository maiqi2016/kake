<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div class="header">
    <div class="menu detail" kk-menu="#menu">
        <img src="<?= $params['frontend_source'] ?>/img/menu.svg"/>
    </div>
</div>