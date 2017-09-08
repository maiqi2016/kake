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
     */
    public function actionIndex()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        $channel = Yii::$app->request->get('channel');
        $channel = Helper::integerDecode($channel);
        if (!$channel) {
            $this->error(Yii::t('common', 'distributor params illegal'));
        }

        // 获取分销商信息
        $producer = $this->getProducer($channel);
        if (empty($producer)) {
            $this->error(Yii::t('common', 'distributor params illegal'));
        }

        // 获取分销产品
        $limit = Yii::$app->params['distribution_limit'];
        $product = $this->service('producer.list-product-ids', [
            'producer_id' => $channel,
            'limit' => $limit
        ]);
        if (empty($product)) {
            $this->error(Yii::t('common', 'the distributor need select product first'));
        }
        $product = $this->listProduct(1, null, DAY, ['ids' => $product]);

        $this->seo([
            'title' => $producer['name'],
            'cover' => current($producer['logo_preview_url'])
        ]);

        return $this->render('index', compact('producer', 'product'));
    }
}
