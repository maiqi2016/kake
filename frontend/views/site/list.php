<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
?>

<?php if (!empty($list)): ?>
    <?php $last = count($list); ?>
    <?php foreach ($list as $i => $standard): ?>
        <li>
            <a href="<?= Url::to([
                'detail/index',
                'id' => $standard['id']
            ]) ?>">
                <div class="photo">
                    <img class="img-responsive" src="<?= current($standard['cover_preview_url']) ?>"/>
                </div>
                <span>￥<?= $standard['price'] ?></span>
                <div class="text">
                    <h2><?= $standard['title'] ?></h2>
                    <small><?= $standard['name'] ?></small>
                </div>
            </a>
        </li>
        
        <li class="thinner-border"></li>
        
    <?php endforeach ?>
<?php endif; ?>