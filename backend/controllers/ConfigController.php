<?php

namespace backend\controllers;

use Oil\src\Helper;
use Yii;
use yii\data\Pagination;
use yii\helpers\Html;

/**
 * 配置管理
 *
 * @auth-inherit-except front sort
 */
class ConfigController extends GeneralController
{
    // 模型
    public static $modelName = 'Config';

    // 模型描述
    public static $modelInfo = '配置';

    /**
     * @inheritdoc
     */
    public static function fileOperation()
    {
        return [
            [
                'text' => '纳入数据库',
                'icon' => 'road',
                'level' => 'primary',
                'method' => 'post',
                'params' => [
                    'app',
                    'key',
                    'value'
                ],
                'value' => 'config/add-form'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增配置',
                'value' => 'config/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'app' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'key' => 'input',
            'value' => 'input',
            'remark' => 'input',
            'state' => [
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'app',
            'key',
            'state'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'app' => [
                'info',
                'code',
                'color' => 'primary'
            ],
            'key' => [
                'code',
                'color' => 'default'
            ],
            'value' => [
                'max-width' => '300px'
            ],
            'remark' => [
                'max-width' => '300px'
            ],
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function fileAssist()
    {
        return [
            'app' => [
                'title' => '所属项目',
                'info',
                'code',
                'color' => 'primary'
            ],
            'key' => [
                'title' => '配置名称',
                'code',
                'color' => 'default'
            ],
            'value' => [
                'title' => '配置值'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function editAssist($action = null)
    {
        return [
            'app' => [
                'elem' => 'select',
                'value' => 0
            ],
            'key',
            'value',
            'remark' => [
                'elem' => 'textarea',
                'placeholder' => '用一句简单语句描述该配置的作用(重要)'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return [
            'order' => ['config.key ASC']
        ];
    }

    /**
     * 项目预配置列表
     */
    public function actionFile()
    {
        $model = parent::model(self::$modelName);
        $handler = function ($config, $app) use ($model) {

            $_config = [];

            foreach ($config as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                if (is_bool($value)) {
                    $_config[$key] = $value ? 1 : 0;
                } else {
                    $_config[$key] = is_string($value) ? Html::encode($value) : $value;
                }
            }

            array_walk($_config, function (&$value, $key) use ($app, $model) {
                $value = [
                    'key' => $key,
                    'app' => $app,
                    'app_info' => $model->_app[$app],
                    'value' => $value
                ];
            });

            return $_config;
        };
        $config = Yii::$app->params;
        $config = $handler($config, 0);

        $frontendConfig = require Yii::getAlias('@frontend/config/params.php');
        $frontendConfig = $handler($frontendConfig, 1);

        $config = array_merge($config, $frontendConfig);
        $config = Helper::arraySort($config, 'key');

        // 分页
        $pagination = new Pagination(['totalCount' => count($config)]);
        $pagination->setPageSize(Yii::$app->params['pagenum']);

        $config = array_slice($config, $pagination->offset, $pagination->limit);
        $config = array_values($config);

        return $this->showListDiy($config, null, $pagination);
    }
}
