<?php

namespace common\components;

use yii\base\Object;

/**
 * Create document for Mysql
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-09-09 10:56:50
 */
class MysqlDocument extends Object
{

    /**
     * @var object Instance of PDO
     */
    public $simplepdo;

    /**
     * @license optional init
     * @license optional change
     * @var array The databases of need create document
     */
    public $databases = [
        'mysql'
    ];

    /**
     * @var string List main info for database
     */
    private $_sqlListMain = <<<EOS
        SELECT
            TABLE_SCHEMA,
            TABLE_NAME,
            COLUMN_NAME,
            COLUMN_DEFAULT,
            IS_NULLABLE,
            COLUMN_COMMENT,
            COLUMN_TYPE
        FROM
            information_schema.COLUMNS
        WHERE
            table_schema IN (
                [DB]
            )
        ORDER BY
            TABLE_SCHEMA ASC,
            TABLE_NAME ASC,
            ORDINAL_POSITION ASC
EOS;

    /**
     * @var string List comment info for database
     */
    private $_sqlListTableComment = <<<EOS
        SELECT
            TABLE_SCHEMA,
            TABLE_NAME,
            TABLE_COMMENT
        FROM
            information_schema. TABLES
        WHERE
            table_schema IN (
                [DB]
            )
EOS;

    /**
     * @var string Get indexs info for database.table
     */
    private $_sqlGetIndex = <<<EOS
        SHOW INDEX
        FROM
            [DB].[TABLE]
EOS;

    /**
     * __constructor
     *
     * @access public
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct();

        $config && $this->databases = $config;
        $this->_replaceDbName();
    }

    /**
     * Create document like array
     *
     * @access public
     * @return array
     */
    public function create()
    {
        $listMain = $this->_listMain();
        $_sqlListTableComment = $this->_listTableComment();

        if (empty($listMain)) {
            return $listMain;
        }

        foreach ($listMain as $dbName => &$table) {
            foreach ($table as $tableName => &$data) {
                $data['comment'] = $_sqlListTableComment[$dbName][$tableName];
                $data['index'] = $this->_getTableIndex($dbName, $tableName);
            }
        }

        return $listMain;
    }

    /**
     * Replace the tag for database name
     *
     * @access private
     * @return void
     * @throws \Exception
     */
    private function _replaceDbName()
    {
        if (empty($this->databases)) {
            throw new \Exception('Has not database need create document');
        }

        $dbName = '\'' . implode(',', $this->databases) . '\'';
        $this->_sqlListMain = str_replace('[DB]', $dbName, $this->_sqlListMain);
        $this->_sqlListTableComment = str_replace('[DB]', $dbName, $this->_sqlListTableComment);
    }

    /**
     * Get main info for database
     *
     * @access private
     * @return array
     */
    private function _listMain()
    {
        $listMain = $this->simplepdo->fetchAll($this->_sqlListMain);

        if (empty($listMain)) {
            return [];
        }

        $main = [];
        foreach ($listMain as $val) {
            $main[$val['TABLE_SCHEMA']][$val['TABLE_NAME']]['fields'][] = [
                'name' => $val['COLUMN_NAME'],
                'type' => $val['COLUMN_TYPE'],
                'comment' => $val['COLUMN_COMMENT'],
                'allowNull' => $val['IS_NULLABLE'] == 'NO' ? 'NOT NULL' : 'ALLOW NULL',
                'default' => $val['COLUMN_DEFAULT']
            ];
        }

        return $main;
    }

    /**
     * Get comment info for database
     *
     * @access private
     * @return array
     */
    private function _listTableComment()
    {
        $listTableComment = $this->simplepdo->fetchAll($this->_sqlListTableComment);

        if (empty($listTableComment)) {
            return [];
        }

        $tableComment = [];
        foreach ($listTableComment as $val) {
            $tableComment[$val['TABLE_SCHEMA']][$val['TABLE_NAME']] = $val['TABLE_COMMENT'];
        }

        return $tableComment;
    }

    /**
     * Get the index and primary for table
     *
     * @access private
     *
     * @param string $dbName
     * @param string $tableName
     *
     * @return array
     */
    private function _getTableIndex($dbName, $tableName)
    {
        $_sqlGetIndex = $this->_sqlGetIndex;
        $_sqlGetIndex = str_replace('[DB]', $dbName, $_sqlGetIndex);
        $_sqlGetIndex = str_replace('[TABLE]', $tableName, $_sqlGetIndex);

        $getIndex = $this->simplepdo->fetchAll($_sqlGetIndex);

        if (empty($getIndex)) {
            return [];
        }

        $index = $_index = [];
        foreach ($getIndex as $key => $val) {
            if (!isset($_index[$val['Key_name']]['fields'])) {
                $_index[$val['Key_name']]['fields'] = '';
            }
            $_index[$val['Key_name']]['fields'] .= $val['Column_name'] . ', ';
            $_index[$val['Key_name']]['type'] = $val['Index_type'];
        }
        foreach ($_index as $key => $val) {
            $index[] = $key . ' : ' . rtrim($val['fields'], ', ') . ' -> ' . $val['type'];
        }

        return $index;
    }
}