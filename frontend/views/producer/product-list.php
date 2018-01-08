<?php
/* @var $this yii\web\View */

use Oil\src\Helper;

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
            <?php $url = $item['link_url_short'] ?>
            <div class="img" >
                <a href="<?= $url ?>">
                    <img src="<?= current($item['cover_preview_url']) ?>"/>
                </a>
                <div class="push">
                    <?php if (!empty($item['referral_link'])): ?>
                        <a class="l" href="<?= $item['referral_link'] ?>">微信推文</a>
                    <?php else: ?>
                        <a class="l hover" href="javascript:void(0)">微信推文</a>
                    <?php endif; ?>
                    <p></p>
                    <a data-clipboard-text="<?= SCHEME . $url ?>" class="r copy" href="javascript:void(0)">复制链接</a>
                </div>
            </div>
            <div class="txt">
                <p class="t1"><a href="<?= $url ?>"><?= $item['title'] ?></a></p>
                <p class="t2"><a href="<?= $url ?>">酒店名称：<?= $item['name'] ?></a></p>
                <p class="t2">分佣金额：<span> <?= Helper::money($item['min_commission']) ?> </span>起</p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
