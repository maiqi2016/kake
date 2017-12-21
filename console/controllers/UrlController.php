<?php

namespace console\controllers;

use console\models\Ad;
use Oil\src\Helper;
use console\models\Attachment;
use console\models\ProductDescription;
use Yii;

/**
 * Url mission
 *
 * @author    <jiangxilee@gmail.com>
 * @copyright 2017-12-21 13:15:45
 */
class UrlController extends GeneralController
{
    /**
     * @var integer Limit for openid list
     */
    public $limit = 20;

    /**
     * Define the params
     *
     * @access public
     *
     * @param string $actionID
     *
     * @return array
     */
    public function options($actionID)
    {
        $params = [];
        switch ($actionID) {
            case 'update-scheme' :
                $params = ['limit'];
                break;
        }

        return $params;
    }

    /**
     * Define the params alias
     *
     * @access public
     * @return array
     */
    public function optionAliases()
    {
        return ['l' => 'limit'];
    }

    /**
     * Update scheme
     *
     * @access public
     */
    public function actionUpdateScheme()
    {
        // 广告表
        $model = new Ad();
        $this->missionProgressForTable(function ($record) use ($model) {
            $reg = '/http:\/\/(pic|www|source)\.kakehotels\.com/i';
            foreach ($record as $item) {
                $model::updateAll([
                    'url' => preg_replace($reg, '//\\1.kakehotels.com', $item['url'])
                ], ['id' => $item['id']]);
            }
        }, $model, [], [
            'id',
            'url'
        ], $this->limit);

        // 附件表
        $model = new ProductDescription();
        $this->missionProgressForTable(function ($record) use ($model) {
            $reg = '/http:\/\/(pic|www|source)\.kakehotels\.com/i';
            foreach ($record as $item) {
                $model::updateAll([
                    'cost' => preg_replace($reg, '//\\1.kakehotels.com', $item['cost']),
                    'recommend' => preg_replace($reg, '//\\1.kakehotels.com', $item['recommend']),
                    'use' => preg_replace($reg, '//\\1.kakehotels.com', $item['use']),
                    'back' => preg_replace($reg, '//\\1.kakehotels.com', $item['back']),
                ], ['id' => $item['id']]);
            }
        }, $model, [], [
            'id',
            'cost',
            'recommend',
            'use',
            'back'
        ], $this->limit);
    }
}