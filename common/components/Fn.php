<?php

namespace common\components;

use function GuzzleHttp\Psr7\str;
use yii\base\Object;

class Fn extends Object
{

    /**
     * Get the micro time
     *
     * @param string $format
     * @param float  $timestamp
     *
     * @return false|string
     */
    public static function date($format = 'Y-m-d H:i:s.u', $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = microtime(true);
        }

        $time = floor($timestamp);
        $micro = round(($timestamp - $time) * 1000);
        $micro = str_pad($micro, 3, 0, STR_PAD_LEFT);

        $format = str_replace('u', $micro, $format);

        return date($format, $time);
    }

    /**
     * Get the day begin and end timestamp
     *
     * @param string $date
     *
     * @return array
     */
    public static function timestampDay($date = null)
    {
        return [
            $begin = strtotime($date ?: date('Y-m-d')),
            $begin + 86400
        ];
    }

    /**
     * Get the week begin and end timestamp
     *
     * @param string $date
     *
     * @return array
     */
    public static function timestampWeek($date = null)
    {
        $date = date('Y-m-d-w', $date ? strtotime($date) : time());
        list($y, $m, $d, $w) = explode('-', $date);

        return [
            mktime(0, 0, 0, $m, $d - $w + 1, $y),
            mktime(23, 59, 59, $m, $d - $w + 7, $y)
        ];
    }

    /**
     * Get the week begin and end day
     *
     * @param string $date
     *
     * @return array
     */
    public static function dayWeek($date = null)
    {
        $date = date('Y-m-d-w', $date ? strtotime($date) : time());
        list($y, $m, $d, $w) = explode('-', $date);

        return [
            $y . '-' . $m . '-' . ($d - $w + 1),
            $y . '-' . $m . '-' . ($d - $w + 7)
        ];
    }

    /**
     * Get the month begin and end timestamp
     *
     * @param string $date
     *
     * @return array
     */
    public static function timestampMonth($date = null)
    {
        $date = date('Y-m-t', $date ? strtotime($date) : time());
        list($y, $m, $t) = explode('-', $date);

        return [
            mktime(0, 0, 0, $m, 1, $y),
            mktime(23, 59, 59, $m, $t, $y)
        ];
    }

    /**
     * Get the month begin and end day
     *
     * @param string $date
     *
     * @return array
     */
    public static function dayMonth($date = null)
    {
        $date = date('Y-m-t', $date ? strtotime($date) : time());
        list($y, $m, $t) = explode('-', $date);

        return [
            $y . '-' . $m . '-01',
            $y . '-' . $m . '-' . $t
        ];
    }

    /**
     * Get the year begin and end timestamp
     *
     * @param string $date
     *
     * @return array
     */
    public static function timestampYear($date = null)
    {
        $y = date('Y', $date ? strtotime($date) : time());

        return [
            mktime(0, 0, 0, 1, 1, $y),
            mktime(23, 59, 59, 12, 31, $y)
        ];
    }
}
