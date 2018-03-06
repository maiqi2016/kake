<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'distribution';
?>

<div class="bg">
    <div class="first clearfix">
        <img class="photo" src="<?= $params['frontend_source'] ?>/img/logo.png"/>
        <div class="detail">
            <a class="left fl" href="#">
                <span>杭州</span>
                <img src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/1.jpg"/>
            </a>
            <div class="center fl">
                <div class="up"><a href="#">热门目的地</a></div>
                <a href="#"><img class="down" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/2.jpg"/></a>
            </div>
            <div class="right fl">
                <a href="#"><img class="up" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/3.jpg"/></a>
                <div class="down"><a href="#"><img class="up yz" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/yangzhou.jpg"/><span>扬州</span></a></div>
            </div>
        </div>
    </div>
    <div class="second clearfix">
        <div class="fl">
            <div class="up">
                <a href="#"><span>北京|西单美爵酒店</span></a>
                <img class="icon" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/icon.svg"/>
            </div>
            <a href="#"><img class="down" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/4.jpg"/></a>
        </div>
        <div class="fr">
            <a href="#"><img class="up" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/5.jpg"/></a>
            <a href="#"><div class="down">杭州|千岛湖安麓酒店</div></a>
            <img class="icon" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/icon.svg"/>
        </div>
    </div>
    <div class="third clearfix">
        <ul class="fl clearfix">
            <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/6.jpg"/></a></li>
            <li><img class="icon" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/icon.svg"/><a href="#">溧阳|涵田度假村</a></li>
            <li><img class="icon" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/icon.svg"/><a href="#">莫干山莫上隐</a></li>
            <li><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/7.jpg"/></a></li>
        </ul>
        <div class="fr">
            <div class="fl"><img class="icon" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/icon.svg"/><a href="#">句容|碧桂园凤凰城酒店</a></div>
            <div class="fr"><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/8.jpg"/></a></div>
        </div>
    </div>
    <div class="four">
        <div class="fl">
            <img class="nb" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/ningbo.jpg"/>
            <div class="txt">
                <img class="icon" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/icon.svg"/>
                <a href="#">宁波|十七房开元观堂</a>
            </div>

        </div>
        <div class="fr">
            <div class="fl"><a href="#"><img src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/9.jpg"/></a></div>
            <div class="fr"><img class="icon" src="<?= $params['frontend_source'] ?>/img/distribution/digital-tv/icon.svg"/><a href="#">杭州|&nbsp&nbsp&nbsp西溪悦椿度假酒店</a></div>
        </div>
    </div>
</div>