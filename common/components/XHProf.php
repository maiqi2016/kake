<?php

namespace common\components;

use yii\base\Object;

/**
 * Library for fackbook/xhprof
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @license   Need enabled extension `xphrof`
 * @copyright 2016-09-18 11:02:09
 */
class XHProf extends Object
{

    /**
     * @var string directory name for save log
     */
    public $dirName = 'xhprof_log';

    /**
     * Enable xhprof
     *
     * @access public
     * @return void
     */
    public function enable()
    {
        // For nginx error: 502
        // xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
    }

    /**
     * Disable xhprof
     *
     * @access public
     * @return integer
     */
    public function disable()
    {
        $data = xhprof_disable();
        $xhprofRuns = new \XHprofRuns_Default();
        $runId = $xhprofRuns->save_run($data, $this->dirName);

        return $runId;
    }
}