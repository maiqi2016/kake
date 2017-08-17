<?php

namespace common\components;

use yii\base\Object;

/**
 * Operation csv file
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-09-18 16:30:57
 */
class Csv extends Object
{

    /**
     * @license optional init
     * @var string csv file
     */
    private $_csvFile;

    /**
     * @var object instance of splObject
     */
    private $_splObject;

    /**
     * @var string error info
     */
    private $_error;

    /**
     * __constructor
     *
     * @access public
     *
     * @param null $csvFile
     */
    public function __construct($csvFile = null)
    {
        parent::__construct();
        $csvFile && $this->_csvFile = $csvFile;
    }

    /**
     * Set csv file
     *
     * @access public
     *
     * @param string $csvFile
     *
     * @return boolean
     */
    public function setCsvFile($csvFile)
    {
        if (!$csvFile || !file_exists($csvFile)) {
            $this->_error = 'Csv file non-set';

            return false;
        }

        $this->_csvFile = $csvFile;
        $this->_splObject = null;

        return true;
    }

    /**
     * Get csv file
     *
     * @access public
     * @return string
     */
    public function getCsvFile()
    {
        return $this->_csvFile;
    }

    /**
     * Check csv file
     *
     * @access private
     *
     * @param string $file
     *
     * @return boolean
     */
    private function _fileValid($file = null)
    {
        $file = $file ? $file : $this->_csvFile;
        if (!$file || !file_exists($file)) {
            $this->_error = 'Csv File invalid';

            return false;
        }

        if (!is_readable($file)) {
            $this->_error = 'Csv File not readable';

            return false;
        }

        return true;
    }

    /**
     * Open csv file
     *
     * @access private
     * @return boolean
     */
    private function _openFile()
    {
        if (!$this->_fileValid()) {
            return false;
        }

        if ($this->_splObject == null) {
            $this->_splObject = new \SplFileObject($this->_csvFile, 'rb');
        }

        return true;
    }

    /**
     * Get data from csv file
     *
     * @access public
     *
     * @param int $length
     * @param int $start
     *
     * @return mixed
     */
    public function getData($length = 0, $start = 0)
    {
        if (!$this->_openFile()) {
            return false;
        }

        $totalLines = $this->getLines();
        $length = $length + $start > $totalLines ? $totalLines + 1 : $length;

        $start = $start < 0 ? 0 : $start;

        $data = [];
        $this->_splObject->seek($start);

        while ($length-- && !$this->_splObject->eof()) {
            $res = $this->_splObject->current();
            $data[] = iconv('gbk', 'utf-8', $res);
            $this->_splObject->next();
        }

        return array_filter($data);
    }

    /**
     * Get lines
     *
     * @access public
     * @return mixed
     */
    public function getLines()
    {
        if (!$this->_openFile()) {
            return false;
        }

        $this->_splObject->seek(filesize($this->_csvFile));

        return $this->_splObject->key();
    }

    /**
     * Get error message
     *
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
}