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
                    <div class="price"><span class="s">￥</span><span class="b"><?= $item['min_price'] ?></span></div>
                    <h2><?= $item['name'] ?></h2>
                    <small><?= $item['title'] ?></small>
                </div> 
            </a>     
        </li>
    <?php endforeach ?>
<?php endif; ?>