<?php

namespace console\controllers;

use common\components\Helper;
use console\models\Attachment;
use Yii;
use yii\helpers\Console;

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

                Yii::$app->oss->upload($path, $file);
            }
        }, $model, [], [
            'deep_path',
            'filename'
        ], $this->limit);
    }
}