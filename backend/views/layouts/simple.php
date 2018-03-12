<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use backend\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title>KAKE后台管理系统</title>
    <?php $this->head() ?>
</head>

<script type="text/javascript">
    var baseUrl = '<?= \Yii::$app->params["backend_url"];?>';
    var requestUrl = '<?= Url::toRoute(['/'], true); ?>';
</script>

<body>
<?php $this->beginBody() ?>

<div id="message"></div>
<?= $content ?>

<?php $this->endBody() ?>

<?php
$controller = \Yii::$app->controller->id;
$action = \Yii::$app->controller->action->id;

$minDirectory = (YII_ENV == 'dev' ? null : null);
$suffix = (YII_ENV == 'dev' ? time() : VERSION);

$sourceUrl = \Yii::$app->params['backend_source'];

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
            $this->{$register}($source . $char . "version=" . $suffix, ['position' => \yii\web\View::POS_HEAD]);
        }
    }
}
?>

</body>
</html>
<?php $this->endPage() ?>
