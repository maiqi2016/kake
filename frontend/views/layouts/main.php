<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);

$app = Yii::$app;
$params = $app->params;

$controller = $app->controller->id;
$action = $app->controller->action->id;

$ngApp = empty($params['ng_app']) ? 'kkApp' : $params['ng_app'];
$ngCtl = empty($params['ng_ctrl']) ? null : (' ng-controller="' . $params['ng_ctrl'] . '"');

$title = empty($params['title']) ? $params['app_title'] : $params['title'];
$shareTitle = empty($params['share_title']) ? $title : $params['share_title'];
$keywords = empty($params['keywords']) ? $params['app_keywords'] : $params['keywords'];
$description = empty($params['description']) ? $params['app_description'] : $params['description'];
$shareDescription = empty($params['share_description']) ? $description : $params['share_description'];
$shareUrl = empty($params['share_url']) ? null : $params['share_url'];

$shareTitle = str_replace('"', '“', $shareTitle);
$shareDescription = str_replace('"', '“', $shareDescription);

$shareCover = empty($params['share_cover']) ? $params['frontend_source'] . '/img/logo.png' : $params['share_cover'];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html ng-app="<?= $ngApp ?>" lang="<?= $app->language ?>">
<head>
    <meta charset="<?= $app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="keywords" content="<?= $keywords ?>">
    <meta name="description" content="<?= $description ?>">
    <?= Html::csrfMetaTags() ?>
    <title><?= $title ?></title>
    <?php $this->head() ?>
</head>

<script type="text/javascript">
    (function setRem() {
        document.documentElement.style.fontSize = document.documentElement.clientWidth / 7.5 + 'px';
    })();
    var baseUrl = '<?= $params["frontend_url"];?>';
    var requestUrl = '<?= $params["frontend_url"] . Url::toRoute(['/']); ?>';
</script>

<body<?= $ngCtl ?>>

<!-- Loading -->
<div id="loading" class="kk-animate kk-show hidden">
    <div class="loading-bar loading-bounce kk-animate kk-t2b-show">
        <div class="in"></div>
        <div class="out"></div>
    </div>
</div>

<!-- Message -->
<div id="message" class="kk-animate kk-show hidden">
    <div class="message-bar kk-animate kk-t2b-show">
        <p class="message-box"></p>
    </div>
</div>

<!-- Hit -->
<div id="hit" class="hidden">
    <div class="hit-bar hit-bounce kk-animate">
        <div class="in"></div>
        <div class="out"></div>
    </div>
</div>

<!-- Body -->
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>

<?php
$minDirectory = (YII_ENV == 'dev' ? null : '_min');
$suffix = (YII_ENV == 'dev' ? time() : VERSION);

$sourceUrl = $params['frontend_source'];
$items = [
    'css',
    'js'
];
foreach ($items as $item) {
    $variable = 'source' . ucfirst($item);
    $register = 'register' . ucfirst($item) . 'File';

    if (is_null($this->context->{$variable}) || 'auto' == $this->context->{$variable}) {
        $source = "/{$item}{$minDirectory}/{$controller}/{$action}.{$item}";
        $this->{$register}($sourceUrl . $source . "?version=" . $suffix);
    } elseif (is_array($this->context->{$variable})) {
        foreach ($this->context->{$variable} as $value) {
            if (strpos($value, '/') === 0 && strpos($value, '//') !== 0) {
                $source = "${sourceUrl}{$value}.{$item}";
            } else if (strpos($value, 'http:') === 0 || strpos($value, 'https:') === 0 || strpos($value, '//') === 0) {
                $source = $value;
            } else {
                $source = "${sourceUrl}/{$item}{$minDirectory}/{$value}.{$item}";
            }

            $char = strpos($source, '?') !== false ? '&' : '?';
            $this->{$register}($source . $char . "version=" . $suffix);
        }
    }
}
?>

<!-- Footer -->
<div class="hidden">
    <span ng-init="common({message: '<?= $app->session->getFlash("message") ?>'})"></span>
    <span ng-init='wxSDK(<?= $app->oil->wx->js->config([
        'hideMenuItems',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'scanQRCode'
    ]) ?>, "<?= $shareTitle ?>", "<?= $shareDescription ?>", "<?= $shareCover ?>", "<?= $shareUrl ?>")'></span>
</div>

<!-- Menu -->
<div class="menu-lm animated hidden">
    <p class="menuclose">KAKE</p>
    <form class="search" id="box1" action="/">
        <input type="hidden" name="r" value="items/index">
        <input type="search" name="keyword" placeholder="目的地">
    </form>
    <a href="<?= Url::toRoute(['site/index']) ?>">
        <img src="<?= $params['frontend_source'] ?>/img/site.svg"/>
        首页
    </a>
    <a href="<?= Url::toRoute(['order/index']) ?>" class="hr">
        <img class="order-center" src="<?= $params['frontend_source'] ?>/img/order-center.svg"/>
        订单中心
    </a>
    <?php if (!empty($this->params['user_info']->role) && $this->params['user_info']->role <= 10): ?>
        <a href="<?= Url::toRoute(['producer/index']) ?>" class="hr">
            <img src="<?= $params['frontend_source'] ?>/img/producer.svg"/>
            分销管理
        </a>
    <?php endif; ?>
    <a href="tel:<?= $params['company_tel'] ?>" class="hr">
        <img src="<?= $params['frontend_source'] ?>/img/phone.svg"/>
        咨询客服
    </a>
    <a href="<?= rtrim($params['passport_url'], '/') ?><?= Url::toRoute([
        'auth/logout',
        'callback' => $params['frontend_url']
    ]) ?>" class="hr">
        <img src="<?= $params['frontend_source'] ?>/img/exit.svg"/>
        退出登录
    </a>
</div>

</body>

<script>
    var _hmt = _hmt || [];
    (function () {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?0dbdcd4d413051d54182fbda00151c4a";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
</html>
<?php $this->endPage() ?>
