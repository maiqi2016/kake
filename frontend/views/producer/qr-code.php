<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<header>
    我的二维码
    <div class="menu detail" kk-menu-lm>
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>

<div class="body">
    <div class="card">
        <div class="out">
            <div class="info">
                <img class="photo" src="<?= current($data[2]['logo_preview_url']) ?>">
                <div class="txt">
                    <p class="info"><?= $data[2]['name'] ?></p>
                    <span class="phone-number"><?= $data[2]['phone'] ?></span>
                </div>
            </div>
            <div class="qr-code">
                <img class="photo" src="<?= $data[1] ?>"/>
            </div>
        </div>
    </div>

    <div class="save">长按图片可保存二维码</div>

</div>