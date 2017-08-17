<?php

namespace common\components;

use yii\base\Object;

/**
 * Logger
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-09-18 21:23:15
 */
class Log extends Object
{

    /**
     * @var integer level for error
     */
    const LV_ERROR = 1;

    /**
     * @var integer level for warning
     */
    const LV_WARNING = 2;

    /**
     * @var integer level for info
     */
    const LV_INFO = 3;

    /**
     * @var integer level for sql
     */
    const LV_SQL = 4;

    /**
     * @var integer level for notice
     */
    const LV_NOTICE = 5;

    /**
     * @var integer level for debug
     */
    const LV_DEBUG = 6;

    /**
     * @var integer level for trace
     */
    const LV_TRACE = 7;

    /**
     * @var integer logger to single file same time logger to aggregate file
     */
    const SINGLE_AND_AGGREGATE = 1;

    /**
     * @var integer logger to single file
     */
    const SINGLE_ONLY = 1;

    /**
     * @var integer logger to aggregate file
     */
    const AGGREGATE_ONLY = 1;

    /**
     * @var array configs
     */
    private $_config = [
        'enabled' => true,
        'logPath' => null,
        'logLv' => self::LV_TRACE,
        'fileNameFormat' => 'Y-m-d',
        'logDateFormat' => 'H:i:s',
        'logFileNameFormat' => 'log-%s-%s.log',
        'logMode' => self::SINGLE_AND_AGGREGATE,
        'logTpl' => '%s: %s:%s --> %s'
    ];

    /**
     * @var string aggregate file path for logs
     */
    private $_aggregateFilePath;

    /**
     * __constructor
     *
     * @access public
     *
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        parent::__construct();

        !empty($config) && $this->_config = array_merge($this->_config, $config);

        if (!is_dir($this->logPath) || !is_writable($this->logPath)) {
            $this->enabled = false;
            throw new \Exception('The log path is not writable');
        } else {
            $this->_aggregateFilePath = $this->_getLogFilePath('aggregate');
        }
    }

    /**
     * Get log file path
     *
     * @access public
     *
     * @param string $name
     *
     * @return string
     */
    private function _getLogFilePath($name)
    {
        $format = $this->fileNameFormat;
        $date = empty($format) ? null : date($format);

        $filename = sprintf($this->logFileNameFormat, $date, $name);
        $filepath = $this->logPath . DIRECTORY_SEPARATOR . $filename;

        return $filepath;
    }

    /**
     * Write log
     *
     * @access protected
     *
     * @param integer $level
     * @param string  $message
     *
     * @return boolean
     */
    protected function _writeLog($level, $message)
    {
        if ($this->enabled === false) {
            return false;
        }

        $name = self::getLevelName($level);
        if (null == $name || $this->logLv < intval($level)) {
            return false;
        }

        $filePath = $this->_getLogFilePath($name);
        // $color = self::getLevelColor($level);

        $format = $this->logDateFormat;
        $date = empty($format) ? null : date($format);
        $ms = str_pad(floor(microtime() * 1000), 4, '0', STR_PAD_LEFT);
        $message = sprintf($this->logTpl . PHP_EOL, str_pad($name, 8, ' ', STR_PAD_LEFT), $date, $ms, $message);

        switch ($this->logMode) {

            case self::SINGLE_AND_AGGREGATE :
                Helper::writeFile($filePath, $message);
                Helper::writeFile($this->_aggregateFilePath, $message);
                break;

            case self::SINGLE_ONLY :
                Helper::writeFile($filePath, $message);
                break;

            case self::AGGREGATE_ONLY :
                Helper::writeFile($this->_aggregateFilePath, $message);
                break;
        }

        return true;
    }

    /**
     * Logger error
     *
     * @access public
     *
     * @param string $message
     *
     * @return void
     */
    public function error($message)
    {
        $this->_writeLog(self::LV_ERROR, $message);
    }

    /**
     * Logger warning
     *
     * @access public
     *
     * @param string $message
     *
     * @return void
     */
    public function warning($message)
    {
        $this->_writeLog(self::LV_WARNING, $message);
    }

    /**
     * Logger info
     *
     * @access public
     *
     * @param string $message
     *
     * @return void
     */
    public function info($message)
    {
        $this->_writeLog(self::LV_INFO, $message);
    }

    /**
     * Logger sql
     *
     * @access public
     *
     * @param string $message
     *
     * @return void
     */
    public function sql($message)
    {
        $this->_writeLog(self::LV_SQL, $message);
    }

    /**
     * Logger notice
     *
     * @access public
     *
     * @param string $message
     *
     * @return void
     */
    public function notice($message)
    {
        $this->_writeLog(self::LV_NOTICE, $message);
    }

    /**
     * Logger debug
     *
     * @access public
     *
     * @param string $message
     *
     * @return void
     */
    public function debug($message)
    {
        $this->_writeLog(self::LV_DEBUG, $message);
    }

    /**
     * Logger trace
     *
     * @access public
     *
     * @param string $message
     *
     * @return void
     */
    public function trace($message)
    {
        $this->_writeLog(self::LV_TRACE, $message);
    }

    /**
     * Get the level name
     *
     * @param integer $level
     *
     * @return string
     */
    static public function getLevelName($level)
    {
        static $levels = [
            self::LV_ERROR => 'error',
            self::LV_WARNING => 'warning',
            self::LV_INFO => 'info',
            self::LV_SQL => 'sql',
            self::LV_NOTICE => 'notice',
            self::LV_DEBUG => 'debug',
            self::LV_TRACE => 'trace'
        ];

        return isset($levels[$level]) ? $levels[$level] : null;
    }

    /**
     * Get the level color
     *
     * @param integer $level
     *
     * @return string
     */
    static public function getLevelColor($level)
    {
        static $levels = [
            self::LV_ERROR => 'red',
            self::LV_WARNING => 'gold',
            self::LV_INFO => 'green',
            self::LV_SQL => 'purple',
            self::LV_NOTICE => 'blue',
            self::LV_DEBUG => 'orange',
            self::LV_TRACE => 'gray'
        ];

        return isset($levels[$level]) ? $levels[$level] : null;
    }

    /**
     * __setter
     *
     * @access public
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_config[$name] = $value;
    }

    /**
     * __getter
     *
     * @access public
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_config[$name];
    }
}