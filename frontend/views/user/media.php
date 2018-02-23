<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'user';
?>

<div style="margin: 100px auto; text-align: center; font-size: 18px;" ng-init="init('<?= $type ?>', '<?= $value ?>')">
    <a href="javascript:void(null);"><?= $type ?>:<?= $value ?></a>
</div>