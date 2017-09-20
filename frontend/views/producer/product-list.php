<?php
/* @var $this yii\web\View */

use common\components\Helper;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<header>
    分销产品列表
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="list" kk-copy-text>

    <?php foreach ($list as $item): ?>
    <div class="product">
        <?php $url = $item['link_url'] . '&channel=' . $item['channel'] ?>
        <div class="img">
            <a href="<?= $url ?>"><img src="<?= current($item['cover_preview_url']) ?>"/></a>
        </div>
        <div class="txt">
            <p class="t1"><a href="<?= $url ?>"><?= $item['title'] ?></a></p>
            <p class="t2">酒店名称：<a href="<?= $url ?>"><?= $item['name'] ?></a></p>
            <p class="t2">分佣金额：<span> <?= Helper::money($item['min_commission']) ?> </span> 起</p>
            <div class="copy" data-clipboard-text="<?= $url ?>">
                <img src="<?= $params['frontend_source'] ?>/img/producer/icon/copy-link.svg">
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>