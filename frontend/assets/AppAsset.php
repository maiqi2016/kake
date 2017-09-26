<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = false;
    public $baseUrl = null;
    public $css = [];
    public $js = [];
    public $depends = [];
    public $jsOptions = [
        'position' => View::POS_END
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->baseUrl = Yii::$app->params['frontend_source'];

        $minDirectory = (YII_ENV == 'dev' ? null : '_min');
        $suffix = (YII_ENV == 'dev' ? time() : VERSION);

        $this->css = [
            "node_modules/bootstrap/dist/css/bootstrap.css?version=" . $suffix,
            "css{$minDirectory}/main.css?version=" . $suffix,
        ];
        $this->js = [
            "node_modules/jquery/dist/jquery.min.js?version=" . $suffix,
            "node_modules/angular/angular.min.js?version=" . $suffix,
            "node_modules/bootstrap/dist/js/bootstrap.js?version=" . $suffix,
            "node_modules/alloytouch/alloy_touch.js?version=" . $suffix,
            "node_modules/alloyfinger/alloy_finger.js?version=" . $suffix,
            "node_modules/alloyfinger/transformjs/transform.js?version=" . $suffix,
            "node_modules/imagesloaded/imagesloaded.pkgd.min.js?version=" . $suffix,
            "js{$minDirectory}/jssdk.js?version=" . $suffix,
            "js{$minDirectory}/main.js?version=" . $suffix,
        ];
    }
}
