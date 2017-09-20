<?php

namespace frontend\controllers;

use common\components\Helper;
use Yii;

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
        $this->sourceJs = [
            '/node_modules/alloytouch/transformjs/asset/tick',
            'site/index'
        ];

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

        $this->seo(['title' => '首页']);

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
     * 微信测试代码
     *
     * @access public
     * @return void
     */
    public function actionWx()
    {
        $wx = Yii::$app->wx;

        // 获取图文 ID
        /*
        $this->dump($wx->material->lists('news'));
        //*/

        // 生成永久二维码
        /*
        $url = $wx->qrcode->url($wx->qrcode->forever('芝妈屋')->ticket);
        $this->dump($url);
        //*/

        // 发送模板消息
        /*
        $wx->notice->send([
            'touser' => $this->user->openid,
            'template_id' => 'sURDDDE9mymmFni3-zKEyPmPl4pid3Ttf42rrnR_8ZI',
            'url' => Yii::$app->params['frontend_url'],
            'data' => [
                'first' => '订单支付成功',
                'keyword1' => '汉庭',
                'keyword2' => 'Leon',
                'keyword3' => '请到平台订单中心预约',
                'keyword4' => '2',
                'keyword5' => '￥768',
                'remark' => '更多问题请联系客服'
            ],
        ]);
        //*/

    }
}
