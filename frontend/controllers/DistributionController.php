<?php

namespace frontend\controllers;

use common\components\Helper;
use Yii;

/**
 * Distribution controller
 */
class DistributionController extends GeneralController
{
    /**
     * Displays index.
     *
     * @access public
     *
     * @param string $channel
     *
     * @return string
     */
    public function actionIndex($channel)
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        list($producer, $uid) = $this->getProducerByChannel($channel);
        if (is_string($producer)) {
            $this->error($producer);
        }

        // 获取分销产品
        $product = $this->listProducerProduct($uid, null, 1, Yii::$app->params['distribution_limit']);
        if (empty($product)) {
            $this->error(Yii::t('common', 'the distributor need select product first'));
        }

        $this->seo([
            'title' => $producer['name'] . '的小店',
            'share_title' => $producer['name'] . '的小店',
            'share_cover' => current($producer['logo_preview_url'])
        ]);

        return $this->render('index', compact('producer', 'product'));
    }

    /**
     * 分销商首页
     *
     * @access public
     *
     * @param string $channel
     *
     * @return string
     */
    public function actionItems($channel)
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        $params = Yii::$app->params;

        $classify = parent::model('product_upstream')->_classify;

        // 焦点图
        $focusList = $this->listAd(2, $params['distribution_ad_focus_limit']);

        // 广告
        $bannerList = $this->listAd(3, $params['distribution_ad_banner_limit']);

        $region = $this->listPlateAndRegion();
        $upstream = $this->listUpstreams(function ($item) {
            $item['name'] = preg_replace('/[ ]+\|[ ]+/', ' ', $item['name']);

            return $item;
        });

        list($producer, $uid) = $this->getProducerByChannel($channel);
        if (is_string($producer)) {
            $this->error($producer);
        }

        $list = $this->listProducerProduct($uid, 0, 1, $params['distribution_items_limit']);
        $top = $this->renderPartial('top-list', ['list' => array_slice($list, 0, 4)]);
        $html_0 = $this->renderPartial('items-list', ['list' => array_slice($list, 4)]);

        $list = $this->listProducerProduct($uid, 1, 1, $params['distribution_items_limit']);
        $html_1 = $this->renderPartial('items-list', compact('list'));

        $list = $this->listProducerProduct($uid, 2, 1, $params['distribution_items_limit']);
        $html_2 = $this->renderPartial('items-list', compact('list'));

        $ref = $this->reference();
        $animate = true;
        if (!empty($ref) && strpos($ref, Yii::$app->params['frontend_url']) !== false) {
            $animate = false;
        }

        $this->seo([
            'title' => $producer['name'],
            'share_title' => $producer['name'],
            'share_cover' => current($producer['logo_preview_url'])
        ]);

        $params = [
            'classify',
            'focusList',
            'bannerList',
            'region',
            'upstream',
            'producer',
            'top',
            'html_0',
            'html_1',
            'html_2',
            'animate'
        ];

        return $this->render('items', compact(...$params));
    }

    public function actionAjaxFocus()
    {
        $this->success([
            'http://kake-file.oss-cn-shanghai.aliyuncs.com/0970-0607-5a138dfa94513.jpg',
            'http://kake-file.oss-cn-shanghai.aliyuncs.com/1219-1713-5a138ef3ae1de.jpg',
            'http://kake-file.oss-cn-shanghai.aliyuncs.com/1527-1893-59fbe1a7da105.png'
        ]);
    }
}
