<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'user';
\Yii::$app->params['title'] = '加入喀客KAKE';
?>

<div class="content">
    <form action="user/apply-distributor" method="post">
        <div class="form-group">
            <label>手机号码</label>
            <input type="email" class="form-control">
        </div>
        <div class="form-group">
            <label>昵称</label>
            <input type="email" class="form-control" placeholder="32个字符内">
        </div>
        <div class="form-group">
            <label>头像图片文件</label>
            <input type="file" class="form-control">
            <p class="help-block">文件大小 ≤3MB</p>
        </div>
        <button type="submit" class="btn btn-default" kk-tap="submitApply()">申请加入</button>
    </form>
</div>