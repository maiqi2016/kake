<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

?>

<div class="title col-sm-offset-1">
    <span class="glyphicon glyphicon-usd"></span> 套餐核销
</div>

<form class="form-horizontal" method="post" action="<?= Url::to(['/order-sold-code/verify-sold-code']) ?>">
    <input name="<?= Yii::$app->request->csrfParam ?>" type="hidden" value="<?= Yii::$app->request->csrfToken ?>">
    <div class="form-group">
        <label class="col-sm-2 control-label">核销码</label>
        <div class="col-sm-3 input-group-lg">
            <input class="form-control" type="text" name="sold">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-2">
            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 确认核销</button>
        </div>
    </div>
    <br>
</form>