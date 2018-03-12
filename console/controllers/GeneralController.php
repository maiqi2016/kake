<?php

namespace console\controllers;

use common\models\Main;
use yii\console\Controller;
use yii\db\ActiveRecord;
use yii\helpers\Console;

/**
 * General controller
 *
 * @author    <jiangxilee@gmail.com>
 * @copyright 2017-05-22 13:38:48
 */
class GeneralController extends Controller
{
    /**
     * Display color
     *
     * @param string $message
     * @param mixed  $colors
     *
     * @return string
     */
    public function color($message, $colors)
    {
        $colors = (array) $colors;
        foreach ($colors as $color) {
            $message = $this->ansiFormat($message, $color);
        }

        return $message;
    }

    /**
     * Display style and printout
     *
     * @access public
     *
     * @param string $message
     * @param array  $params
     * @param mixed  $style
     * @param mixed  $begin
     * @param mixed  $end
     *
     * @return void
     */
    public function console($message, $params = [], $style = null, $begin = null, $end = null)
    {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        $message = sprintf($message, ...$params);
        $message = ($begin ?: PHP_EOL) . $message . ($end ?: null);
        $this->stdout($message, $style);
    }

    /**
     * Common mission progress with table
     *
     * @access public
     *
     * @param callable $logicFn
     * @param object   $model
     * @param array    $where
     * @param mixed    $select
     * @param integer  $limit
     *
     * @return integer
     */
    public function missionProgressForTable($logicFn, $model, $where = [], $select = '*', $limit = 20)
    {
        /**
         * Handler core
         *
         * @param integer $page
         */
        $handler = function ($page = 1) use ($logicFn, $model, $where, $select, $limit, &$handler) {

            /**
             * @var $model ActiveRecord
             */
            $page = intval($page) > 0 ? $page : 1;

            $count = $model::find()->where($where)->count();
            $length = strlen($count);
            $totalPage = ceil($count / $limit);

            $result = $model::find()->select($select)->where($where)->offset(($page - 1) * $limit)->limit($limit)->asArray()->all();

            try {
                $recursion = call_user_func($logicFn, $result);
            } catch (\Exception $e) {
                $msg = $this->color($e->getMessage(), Console::FG_RED);
                $this->console($msg);
                $recursion = false;
            }

            $progress = $page * $this->limit;
            $progress = $progress > $count ? $count : $progress;
            $progress = str_pad($progress, $length, 0, STR_PAD_LEFT) . ' / ' . $count;

            $this->console('Task completion ：%s', [
                $this->color($progress, Console::FG_GREEN)
            ], null, null, $page == $totalPage ? PHP_EOL : null);

            if (count($result) == $this->limit && $recursion !== false) {
                $handler(++$page);
            }
        };
        $handler();

        return self::EXIT_CODE_NORMAL;
    }
}