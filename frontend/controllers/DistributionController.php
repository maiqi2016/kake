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
        $product = $this->listProducerProduct($uid, 1, Yii::$app->params['distribution_limit']);
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

        $region = $this->listPlateAndRegion();
        $hotel = $this->listHotels(function ($item) {
            $item['name'] = preg_replace('/[ ]+\|[ ]+/', ' ', $item['name']);

            return $item;
        });

        list($producer, $uid) = $this->getProducerByChannel($channel);
        if (is_string($producer)) {
            $this->error($producer);
        }

        $top = null;
        list($html, $over) = $this->renderItemsPage($uid, 1, function ($list, $limit) use (&$top) {
            $topNumber = count($list) >= 5 ? 4 : 2;
            $top = array_slice($list, 0, $topNumber);

            return [
                array_slice($list, $topNumber),
                $limit - $topNumber
            ];
        });

        $this->seo([
            'title' => $producer['name'] . '的小店',
            'share_title' => $producer['name'] . '的小店',
            'share_cover' => current($producer['logo_preview_url'])
        ]);

        return $this->render('items', compact('region', 'hotel', 'producer', 'top', 'html', 'over', 'uid'));
    }

    /**
     * ajax 获取下一页列表
     */
    public function actionAjaxItems()
    {
        $uid = Yii::$app->request->post('uid');
        $page = Yii::$app->request->post('page');

        list($html, $over) = $this->renderItemsPage($uid, $page);
        $this->success(compact('html', 'over'));
    }

    /**
     * 渲染列表视图并返回 html
     *
     * @access private
     *
     * @param integer  $uid
     * @param integer  $page
     * @param callable $callback
     *
     * @return array
     */
    private function renderItemsPage($uid, $page, $callback = null)
    {
        $pageSize = Yii::$app->params['distribution_items_limit'];
        $list = $this->listProducerProduct($uid, $page, $pageSize);
        if ($callback) {
            list($list, $pageSize) = call_user_func_array($callback, [
                $list,
                $pageSize
            ]);
        }
        $content = $this->renderPartial('items-list', compact('list'));

        return [
            $content,
            count($list) < $pageSize
        ];
    }
}
