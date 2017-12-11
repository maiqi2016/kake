<?php

namespace console\controllers;

use Oil\src\Helper;
use console\models\Attachment;
use console\models\ProductDescription;
use Yii;

/**
 * Attachment mission
 *
 * @author    <jiangxilee@gmail.com>
 * @copyright 2017-11-16 11:27:02
 */
class AttachmentController extends GeneralController
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
            case 'upload-to-oss' :
            case 'update-text-for-oss' :
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
     * Upload file to Ali OSS
     *
     * @access public
     */
    public function actionUploadToOss()
    {
        $model = new Attachment();

        $this->missionProgressForTable(function ($record) {
            foreach ($record as $item) {
                $deep = str_replace('-', DIRECTORY_SEPARATOR, $item['deep_path']);
                $path = '/Users/Leon/Desktop/abc/' . $deep . DIRECTORY_SEPARATOR . $item['filename'];

                $file = $item['deep_path'] . '-' . $item['filename'];

                Yii::$app->oil->oss->upload($path, $file);
            }
        }, $model, [], [
            'deep_path',
            'filename'
        ], $this->limit);
    }

    /**
     * Update Content for OSS
     *
     * @access public
     */
    public function actionUpdateTextForOss()
    {
        $model = new ProductDescription();

        $this->missionProgressForTable(function ($record) use ($model) {

            $reg = '/http:\/\/pic\.kakehotels\.com\/upload\/kake\/(\d+)\/(\d+)\/(.*)\.(jpg|jpeg|png|gif)/iU';
            foreach ($record as $item) {
                $handler = function ($k) use ($item, $reg) {
                    $text = $item[$k];
                    preg_match_all($reg, $text, $match);

                    foreach ($match[0] as $key => $src) {
                        $newSrc = 'http://pic.kakehotels.com/' . $match[1][$key] . '-' . $match[2][$key] . '-' . $match[3][$key] . '.' . $match[4][$key];
                        $text = str_replace($src, $newSrc, $text);
                    }

                    return $text;
                };

                $model::updateAll([
                    'cost' => $handler('cost'),
                    'recommend' => $handler('recommend'),
                    'use' => $handler('use'),
                    'back' => $handler('back'),
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