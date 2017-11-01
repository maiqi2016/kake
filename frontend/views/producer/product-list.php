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

<div class="body" kk-copy-text>

    <?php foreach ($list as $item): ?>
        <div class="product clearfix">
            <?php $url = $item['link_url'] . '&channel=' . $item['channel'] ?>
            <a class="img" href="<?= $url ?>">
                <img src="<?= current($item['cover_preview_url']) ?>"/>
            </a>
            <div class="txt">
                <p class="t1"><a href="<?= $url ?>"><?= $item['title'] ?></a></p>
                <p class="t2"><a href="<?= $url ?>">酒店名称：<?= $item['name'] ?></a></p>
                <p class="t2">分佣金额：<span> <?= Helper::money($item['min_commission']) ?> </span>起
                    <img class="copy" data-clipboard-text="<?= $url ?>"
                         src="<?= $params['frontend_source'] ?>/img/producer/icon/copy-link.svg">
                </p>

                <!-- 团团看这里 -->
                <?php if (!empty($item['referral_link'])): ?>
                    <a href="<?= $item['referral_link'] ?>">查看介绍</a>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>
</div>
