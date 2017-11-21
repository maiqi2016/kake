<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
?>

<?php if (!empty($list)): ?>
    <?php foreach ($list as $item): ?>
        <li>
            <a href="<?= Url::to(['detail/index', 'id' => $item['id']]) ?>">
                <div class="photo">
                    <img src="<?= current($item['cover_preview_url']) ?>">
                    <span class="price"><i>￥</i><?= $item['min_price'] ?></span>
                    <p><?= $item['name'] ?></p>
                </div>
            </a>
            <div class="hot"></div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>