<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
?>

<?php if (!empty($list)): ?>
    <?php foreach ($list as $item): ?>
        <div class="recommend3">
            <div class="recommend3-1">
                <a href="<?= Url::to(['detail/index', 'id' => $item['id']]) ?>">
                    <img class="img-responsive" src="<?= current($item['cover_preview_url']) ?>"/>
                </a>
                <div class="recommend3-1-1">￥<span><?= $item['min_price'] ?></span></div>
            </div>
            <div class="recommend3-2">
                <?= $item['title'] ?>
            </div>
            <div class="recommend3-3">
                <?= $item['name'] ?>
            </div>
        </div>
    <?php endforeach ?>
<?php endif; ?>

<header>
     全部目的地
    <div class="menu detail" kk-menu="#menu">
        <img class="img-responsive" src="<?= $params['frontend_source'] ?>/img/list.svg"/>
    </div>
</header>