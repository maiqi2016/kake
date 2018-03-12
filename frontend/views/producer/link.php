<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<header>
    我的推广链接
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body">

    <div class="blank"></div>
    <div class="link" kk-copy-text>
        <div class="txt">推广链接</div>

        <?php foreach ($links as $item): ?>
            <div class="popularize-link">
                <p><?= $item ?></p>
                <div class="copy" data-clipboard-text="<?= $item ?>">
                    <img src="<?= $params['frontend_source'] ?>/img/producer/icon/copy-link.svg">
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>    