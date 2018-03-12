<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'order';
?>

<header>
    订单中心
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>
<div class="classify">
    <div class="classify-1">
        <a class="classify-1-1" href="<?= Url::toRoute([
            'order/index',
            'type' => 'ongoing'
        ]) ?>">
            <div>
                <span>进行中</span>
            </div>
        </a>
        <a href="<?= Url::toRoute([
            'order/index',
            'type' => 'completed'
        ]) ?>">
            <div class="cur-1">
                <span>已完成</span>
            </div>
        </a>
    </div>
</div>

<div class="blank-piece"></div>

<div class="order-complete" kk-ajax-load="order/ajax-list" data-params="type=completed" data-over="<?= $over ?>">
    <?= trim($html) ? $html : '<div class="no-data">暂无相关订单</div>' ?>
</div>
