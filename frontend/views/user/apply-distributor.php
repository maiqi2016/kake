<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'user';
?>

<div class="blank-div"></div>
<div class="apply-bg">
    <div class="form-group phone">
        <label>手机号码</label>
        <input class="form-control" ng-model="apply.phone" placeholder="输入电话号码">
    </div>
    <div class="form-group name">
        <label pl>昵称</label>
        <input class="form-control" ng-model="apply.name" placeholder="32个字符内">
    </div>
    <input type="hidden" ng-model="apply.attachment">
    <div class="form-group file" kk-ajax-upload="div#file" data-action="user/upload-avatar" data-callback="handleUpload">
        <label>头像文件</label>
        <div id="file" class="form-control">{{apply.tip}}</div>
        <p class="help-block">文件大小 ≤3MB</p>
    </div>
    <center><button class="btn btn-default" kk-tap="submitApply()">申请加入</button></center>
</div>