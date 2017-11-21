<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = false;
    public $baseUrl = null;
    public $css = [];
    public $js = [];
    public $depends = [];
    public $jsOptions = ['position' => yii\web\View::POS_HEAD];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->baseUrl = Yii::$app->params['backend_source'];

        $minDirectory = (YII_ENV == 'dev' ? null : null);
        $suffix = (YII_ENV == 'dev' ? time() : VERSION);

        $this->css = [
            "node_modules/bootstrap/dist/css/bootstrap.css?version=" . $suffix,
            "node_modules/bootstrap/dist/css/bootstrap-theme.css?version=" . $suffix,
            "node_modules/perfect-scrollbar/css/perfect-scrollbar.css?version=" . $suffix,
            "css{$minDirectory}/main.css?version=" . $suffix,
        ];
        $this->js = [
            "node_modules/jquery/dist/jquery.min.js?version=" . $suffix,
            "node_modules/bootstrap/dist/js/bootstrap.js?version=" . $suffix,
            "node_modules/perfect-scrollbar/dist/perfect-scrollbar.min.js?version=" . $suffix,
            "js{$minDirectory}/main.js?version=" . $suffix,
        ];
    }
}
