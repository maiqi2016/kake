<?php

namespace common\components;

use yii\base\Object;

/**
 * Pagination for front
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-09-18 16:53:18
 */
class Pagination extends Object
{

    /**
     * @var array configs
     */
    protected $_config = [
        'method' => 'GET',
        'pageSize' => 20,
        'totalNumber' => 0,
        'pageLinksNumber' => 10,
        'showTotalPage' => true,
        'showLiftInput' => true,
        'minPageShowLiftInput' => 10,
        'paramKeyOfPage' => 'page'
    ];

    /**
     * @var array with params when location
     */
    private $_parameter = [];

    /**
     * @var integer total page
     */
    private $_totalPage;

    /**
     * @var string current url
     */
    private $_url;

    /**
     * @var integer current page number
     */
    private $_currentPage = 1;

    /**
     * @var string javascript for lift
     */
    private $_liftJavascript = <<<EOJ
        if (event.keyCode == 13) {
            if (!this.value || this.value == 0) { 
                return false;
            }
            var lift = '%s'.replace('LIFT', this.value);
            location = lift;
            return false;
        }
EOJ;

    /**
     * @var array tpl for style - bootstrap
     */
    protected $_tpl = [
        'prev' => 'Old',
        'next' => 'Next',
        'first' => '<<',
        'last' => '>>',
        'theme' => '$FIRST$ $PRE_PAGE$ $LINKS$ $LIFT$ $NEXT_PAGE$ $LAST$',
        'prev_html' => '<li><a href="%s">%s</a></li>',
        'next_html' => '<li><a href="%s">%s</a></li>',
        'first_html' => '<li><a href="%s">%s</a></li>',
        'last_html' => '<li><a href="%s">%s</a></li>',
        'theme_html' => '<nav class="navbar-right navbertop"><ul class="pagination">%s</ul></nav>',
        'lift_html' => '<input class="form-control pull-left pages_lift" onkeydown="%s">',
        'link_html' => '<li><a href="%s">%s</a></li>',
        'active_link_html' => '<li class="active"><a href="javascript:void(0);">%s</a></li>'
    ];

    /**
     * @var boolean ajax action
     */
    protected $_ajaxFunction = null;

    /**
     * __constructor
     *
     * @access public
     *
     * @param array  $config
     * @param array  $parameter
     * @param array  $tpl
     * @param string $ajaxFunction
     */
    public function __construct($config = [], $parameter = [], $tpl = [], $ajaxFunction = null)
    {
        parent::__construct();

        $config && $this->_config = array_merge($this->_config, $config);
        $tpl && $this->_tpl = array_merge($this->_tpl, $tpl);

        switch (strtoupper($this->method)) {
            case 'GET' :
                $method = $_GET;
                break;

            case 'POST' :
                $method = $_POST;
                break;

            default :
                $method = [];
        }

        $this->_parameter = empty($parameter) ? $method : $parameter;

        $currentPage = isset($method[$this->paramKeyOfPage]) ? intval($method[$this->paramKeyOfPage]) : 1;
        $this->_currentPage = $currentPage > 0 ? $currentPage : 1;

        $ajaxFunction && $this->_ajaxFunction = $ajaxFunction;
    }

    /**
     * Handle url
     *
     * @access private
     *
     * @param $page
     *
     * @return string
     */
    private function _locationUrl($page)
    {
        if ($this->_ajaxFunction) {
            $url = 'javascript:void(0);" onClick="' . $this->_ajaxFunction . '(' . $page . ', this);';
        } else {
            $url = str_replace('$PAGE$', $page, $this->_url);
        }

        return $url;
    }

    /**
     * Create uri with array
     *
     * @access public
     *
     * @param array $parameter
     *
     * @return string
     */
    public function url($parameter)
    {
        $params = '';
        array_walk($parameter, function ($value, $key) use (&$params) {
            $params .= '&' . $key . '=' . $value;
        });

        $params = '?' . ltrim($params, '&');

        return $params;
    }

    /**
     * Assembly url
     *
     * @access public
     *
     * @param string $ajaxFunction
     *
     * @return string
     */
    public function html($ajaxFunction = null)
    {
        $this->_ajaxFunction = $ajaxFunction;

        if (0 == $this->totalNumber) {
            return null;
        }

        $this->_parameter[$this->paramKeyOfPage] = '$PAGE$';
        $this->_url = $this->url($this->_parameter);

        // Page info
        $this->_totalPage = ceil($this->totalNumber / $this->pageSize);
        if (!empty($this->_totalPage) && $this->_currentPage > $this->_totalPage) {
            $this->_currentPage = $this->_totalPage;
        }

        $nowCoolPage = $this->pageLinksNumber / 2;
        $nowCoolPageCeil = ceil($nowCoolPage);
        $this->showTotalPage && ($this->_tpl['last'] = $this->_totalPage);

        // Pre
        $upRow = $this->_currentPage - 1;
        $upPage = ($upRow > 0 && !empty($this->_tpl['prev'])) ? sprintf($this->_tpl['prev_html'], $this->_locationUrl($upRow), $this->_tpl['prev']) : null;

        // Lift
        $lift = sprintf($this->_tpl['lift_html'], $this->_liftJavascript);
        $input = sprintf($lift, $this->_locationUrl('LIFT'));
        $input = ($this->showLiftInput && $this->minPageShowLiftInput && $this->_totalPage > $this->minPageShowLiftInput) ? $input : null;

        // Next
        $downRow = $this->_currentPage + 1;
        $downPage = ($downRow <= $this->_totalPage && !empty($this->_tpl['next'])) ? sprintf($this->_tpl['next_html'], $this->_locationUrl($downRow), $this->_tpl['next']) : null;

        // First
        $theFirst = null;
        if ($this->_totalPage > $this->pageLinksNumber && ($this->_currentPage - $nowCoolPage) >= 1 && !empty($this->_tpl['first'])) {
            $theFirst = sprintf($this->_tpl['first_html'], $this->_locationUrl(1), $this->_tpl['first']);
        }

        // Last
        $theEnd = null;
        if ($this->_totalPage > $this->pageLinksNumber && ($this->_currentPage + $nowCoolPage) < $this->_totalPage && !empty($this->_tpl['last'])) {
            $theEnd = sprintf($this->_tpl['last_html'], $this->_locationUrl($this->_totalPage), $this->_tpl['last']);
        }

        // Page links
        $linkPage = '';
        for ($i = 1; $i <= $this->pageLinksNumber; $i++) {

            if (($this->_currentPage - $nowCoolPage) <= 0) {
                $page = $i;
            } else {
                if (($this->_currentPage + $nowCoolPage - 1) >= $this->_totalPage) {
                    $page = $this->_totalPage - $this->pageLinksNumber + $i;
                } else {
                    $page = $this->_currentPage - $nowCoolPageCeil + $i;
                }
            }

            if ($page > 0 && $page != $this->_currentPage) {
                if ($page <= $this->_totalPage) {
                    $linkPage .= sprintf($this->_tpl['link_html'], $this->_locationUrl($page), $page);
                } else {
                    break;
                }
            } else {
                if ($page > 0 && $this->_totalPage != 1) {
                    $linkPage .= sprintf($this->_tpl['active_link_html'], $page);
                }
            }
        }

        // Replace tpl
        $variable = str_replace([
            '$NOW_PAGE$',
            '$PRE_PAGE$',
            '$LIFT$',
            '$NEXT_PAGE$',
            '$FIRST$',
            '$LINKS$',
            '$LAST$',
            '$TOTAL_NUMBER$'
        ], [
            $this->_currentPage,
            $upPage,
            $input,
            $downPage,
            $theFirst,
            $linkPage,
            $theEnd,
            $this->totalNumber
        ], $this->_tpl['theme']);

        return sprintf($this->_tpl['theme_html'], $variable);
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