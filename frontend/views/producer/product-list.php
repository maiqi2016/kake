<?php
/* @var $this yii\web\View */

use common\components\Helper;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<header>
    分销产品列表
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body" kk-copy-text>

    <?php foreach ($list as $item): ?>
    <div class="product">
        <div class="img">
            <img src="<?= current($item['cover_preview_url']) ?>"/>
        </div>
        <?php $url = $item['link_url'] . '&channel=' . $item['channel'] ?>
        <div class="txt">
            <p class="t1"><a href="<?= $url ?>"><?= $item['title'] ?></a></p>
            <p class="t2">酒店名称：<?= $item['name'] ?></p>
            <p class="t2">分佣金额：<?= Helper::money($item['min_commission']) ?>起</p>
            <div class="copy" data-clipboard-text="<?= $url ?>">
                <img src="<?= $params['frontend_source'] ?>/img/producer/icon/copy-link.svg">
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>