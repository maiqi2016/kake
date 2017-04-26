<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'generic';
?>

<header>
    <a href="javascript:history.go(-1);" class="return">
        <img class="img-responsive"
             src="<?= $params['frontend_source'] ?>/img/return.svg"/>
    </a>
    列表
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive"
             src="<?= $params['frontend_source'] ?>/img/menu1.svg"/>
    </div>
</header>
<div class="body">
    <div class="recommend" kk-ajax-load="items/ajax-list" blank-message="没有更多的数据供加载~">
        <?= $html ?>
    </div>
</div>





