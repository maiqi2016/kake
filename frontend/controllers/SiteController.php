<?php

namespace frontend\controllers;

use common\components\Helper;
use Yii;
use yii\helpers\Url;

/**
 * Site controller
 */
class SiteController extends GeneralController
{
    /**
     * Displays homepage.
     */
    public function actionIndex()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        $params = Yii::$app->params;

        // 焦点图
        $focusList = $this->listProductFocus($params['site_focus_limit']);
        $focusList = array_merge($focusList, $this->listAd(0, $params['site_ad_focus_limit']));
        $focusList = Helper::arraySort($focusList, 'sort', 'ASC');

        // 板块
        $plateList = $this->listPlate();

        // 闪购模块
        $flashSalesList = $this->listProduct(1, $params['site_sale_limit'], 0, [
            'manifestation' => 2
        ]);

        // banner 模块
        $bannerList = $this->listAd(1, $params['site_ad_banner_limit']);

        // 精品推荐
        list($standardHtml, $over) = $this->renderListPage(1);

        $this->seo([
            'title' => '首页',
            'share_title' => Yii::$app->params['app_title'],
            'share_description' => Yii::$app->params['app_description'],
        ]);

        return $this->render('index', compact('focusList', 'plateList', 'flashSalesList', 'bannerList', 'standardHtml', 'over'));
    }

    /**
     * ajax 获取下一页列表
     */
    public function actionAjaxList()
    {
        $page = Yii::$app->request->post('page');

        list($html, $over) = $this->renderListPage($page);
        $this->success(compact('html', 'over'));
    }

    /**
     * 渲染列表视图并返回 html
     *
     * @access private
     *
     * @param integer $page
     *
     * @return array
     */
    private function renderListPage($page)
    {
        $pageSize = Yii::$app->params['site_product_limit'];
        $list = $this->listProduct($page, $pageSize, DAY, [
            'manifestation' => 0
        ]);
        $content = $this->renderPartial('list', compact('list'));

        return [
            $content,
            count($list) < $pageSize
        ];
    }

    /**
     * 测试代码片段
     *
     * @access public
     * @return void
     */
    public function actionDebug()
    {
        $wx = Yii::$app->wx;

        // 获取图文 ID
        /*
        $this->dump($wx->material->lists('news'));
        //*/
    }
}
