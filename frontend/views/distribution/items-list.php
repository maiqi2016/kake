<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
?>

<?php if (!empty($list)): ?>
    <?php foreach ($list as $item): ?>
        <li>
            <a href="<?= Url::to(['detail/index', 'id' => $item['id']]) ?>">
                <div class="photo">
                    <div class="outImg"><img src="<?= current($item['cover_preview_url']) ?>"></div>
                    <span class="price"><i>￥</i> <?= $item['min_price'] ?> </span>
                    <h2><?= $item['name'] ?></h2>
                    <small><?= $item['title'] ?></small>
                </div> 
            </a>     
        </li>
    <?php endforeach ?>
<?php endif; ?>