<?php

namespace common\components;

use yii\base\Object;

/**
 * Sphinx for fulltext search
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-08-19 17:19:51
 * @license   You should install the extend of sphinx or require sphinxapi.php
 *            mac osx
 *            brew install sphinx --whit-mysql
 *            brew install php56-sphinx
 */
class Sphinx extends Object
{

    // instance of SphinxClient()
    private $_client;

    /**
     * @license optional init
     * @var array config for sphinx
     */
    private $_config = [
        'mode' => SPH_MATCH_ALL,
        'host' => '127.0.0.1',
        'port' => 9312,
        'index' => '*',
        'groupby' => '',
        'groupby_sort' => '@group desc',
        'filter' => '',
        'filter_values' => [],
        'distinct' => '',
        'sortby' => '',
        'sortby_expression' => '',
        'offset' => 0,
        'limit' => 20,
        'ranker' => SPH_RANK_PROXIMITY_BM25,
        'select' => ''
    ];

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

        $config && $this->_config = array_merge($this->_config, $config);
        $this->_client = new \SphinxClient();
    }

    /**
     * 搜索操作
     *
     * @access public
     *
     * @param string $query
     * @param array  $config
     *
     * @return array
     */
    public function search($query = null, $config = [])
    {

        $config && $this->_config = array_merge($this->_config, $config);

        // The host and port of service
        $this->_client->SetServer($this->host, $this->port);
        $this->_client->SetConnectTimeout(1);

        // Set the return type to array
        $this->_client->SetArrayResult(true);

        $this->_client->SetMatchMode($this->mode);

        if (count($this->filter_values)) {
            $this->_client->SetFilter($this->filter, $this->filter_values);
        }

        if ($this->groupby) {
            $this->_client->SetGroupBy($this->groupby, SPH_GROUPBY_ATTR, $this->groupby_sort);
        }

        if ($this->sortby) {
            $this->_client->SetSortMode(SPH_SORT_EXTENDED, $this->sortby);
        }

        if ($this->sortby_expression) {
            $this->_client->SetSortMode(SPH_SORT_EXPR, $this->sortby_expression);
        }

        if ($this->distinct) {
            $this->_client->SetGroupDistinct($this->distinct);
        }

        if ($this->select) {
            $this->_client->SetSelect($this->select);
        }

        if ($this->limit) {
            $this->_client->SetLimits($this->offset, $this->limit, ($this->limit > 1000) ? $this->limit : 1000);
        }

        $this->_client->SetRankingMode($this->ranker);
        $result = $this->_client->Query($query, $this->index);

        if ($result === false) {
            return [
                'status' => 0,
                'info' => 'Query failed: ' . $this->_client->GetLastError(),
                'data' => null
            ];
        }

        $info = null;
        if ($this->_client->GetLastWarning()) {
            $info = 'Warning: ' . $this->_client->GetLastWarning();
        }

        return [
            'status' => 1,
            'info' => $info,
            'data' => $result
        ];
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
