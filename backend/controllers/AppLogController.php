<?php

namespace backend\controllers;

use Oil\src\Helper;
use yii\helpers\Html;

/**
 * 运行日志管理
 *
 * @auth-inherit-except add edit front sort
 */
class AppLogController extends GeneralController
{
    // 模型
    public static $modelName = 'AppLog';

    // 模型描述
    public static $modelInfo = '运行日志';

    /**
     * @var array Hook
     */
    public static $hookDateSection = ['log_time' => 'stamp'];

    /**
     * @inheritdoc
     */
    public static function indexOperation()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function indexFilter()
    {
        return [
            'level' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'log_time' => [
                'elem' => 'input',
                'type' => 'date',
                'between' => true
            ],
            'message' => 'input'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexSorter()
    {
        return [
            'log_time'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function indexAssist()
    {
        return [
            'id' => 'code',
            'level' => [
                'min-width' => '70px',
                'info',
                'code',
                'tip'
            ],
            'log_time' => [
                'min-width' => '170px',
                'html'
            ],
            'prefix' => 'tip',
            'message' => 'html',
        ];
    }

    /**
     * @inheritdoc
     */
    public function indexCondition($as = null)
    {
        return [
            'order' => [
                'log_time DESC',
                'id DESC'
            ]
        ];
    }

    /**
     * 将信息处理成带格式显示
     *
     * @access private
     *
     * @param string $message
     *
     * @return string
     */
    private function handleMessageForView($message)
    {
        $message = preg_replace('/#(\d+) /', '[#$1]', $message);
        $message = Html::encode($message);
        $message = preg_replace('/\[#(\d+)\]/', '<br><b>#$1 --> </b>', $message);

        $message = str_replace('Stack trace:', '<br><br><b>Stack trace:</b><br>', $message);
        $message = str_replace('Next exception', '<br><br><b>Next</b>exception', $message);
        $message = str_replace('exception &', '<br><br>exception &', $message);

        $message = preg_replace('/^(\<br\>\<br\>)/', '', $message);
        $message = preg_replace('/\\n/', '<br>', $message);

        return $message;
    }

    /**
     * @inheritdoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (!empty($record['prefix'])) {
            $record['prefix'] = Helper::cutString($record['prefix'], [
                '[^1',
                ']^0'
            ]);
        }

        if (!empty($record['message'])) {
            $record['message'] = $this->handleMessageForView($record['message']);
        }

        return parent::sufHandleField($record, $action);
    }
}
