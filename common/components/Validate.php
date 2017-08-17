<?php

namespace common\components;

use yii\base\Object;

/**
 * Validate components
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-3-9 10:43:59
 */
class Validate extends Object
{

    /**
     * @license optional init
     * @var array Config
     */
    protected $_config = [
        'data' => null,
        'method' => 'POST',
        'return_error_first_time' => true,
        'set_unset_param_value_null' => false,
        'param_field_split' => ':',
        'functions_split' => '+',
        'params_left_separator' => '(',
        'params_right_separator' => ')',
        'params_split' => ',',
        'callback_function_prefix' => 'callback_'
    ];

    /**
     * @license optional init
     * @var array Lang
     */
    protected $_lang = [
        'function_un_exists' => '验证方法%s不存在',
        'rules_illegal' => '验证规则数组必须为非空数组',
        'is_array' => '%s参数必须是一个非空数组',
        'in_array' => '%s参数必须是指定数组中的一个值',
        'required' => '%s参数不能为空',
        'email' => '%s不符合email地址格式 %s',
        'url' => '%s不符合url地址格式',
        'ip' => '%s不符合ip地址格式',
        'min_length' => '%s字串长度不能小于%s',
        'max_length' => '%s字串长度不能大于%s',
        'between_length' => '%s字串长度必须在%s~%s之间',
        'exact_length' => '%s字串长度必须等于%s',
        'alpha' => '%s仅允许输入字母',
        'alpha_numeric' => '%s仅允许输入字母和数字',
        'alpha_dash' => '%s仅允许输入字母、数字、下划线和中划线',
        'numeric' => '%s仅允许输入数值类型',
        'natural' => '%s必须为自然数',
        'natural_no_zero' => '%s必须为非零自然数',
        'lt' => '%s值必须小于%s',
        'lt_eq' => '%s值必须小于等于%s',
        'gt' => '%s值必须大于%s',
        'gt_eq' => '%s值必须大于等于%s',
        'eq' => '%s值必须等于%s',
        'between' => '%s数值必须在%s~%s之间',
        'id_card' => '%s不符合身份证号码格式',
        'post_code' => '%s不符合邮编格式',
        'phone' => '%s不符合手机号码格式',
        'qq' => '%s不符合qq号码格式',
        'chinese' => '%s仅允许输入中文',
        'special_exists' => '%s不允许有特殊字符',
        'html_tag_exists' => '%s不允许含html标签',
        'time_string' => '%s为不合法时间戳',
        'json_string' => '%s为不合法时间戳',
        'in_mysql_int_signed' => '%s值超出整型取值范围',
        'in_mysql_int' => '%s值超出无符号整型取值范围'
    ];

    /**
     * @define string Separator of function sprintf params
     */
    const SPRINTF_OFS = '#';

    /**
     * @license optional init
     * @var array Params allow from method
     */
    protected $_allowMethod = [
        'GET',
        'POST',
        'GET_POST'
    ];

    /**
     * @var array Data need validate
     * @var array Data of validated
     */
    protected $_data = [];

    protected $_validatedData = [];

    /**
     * @var array Errors
     */
    protected $_error = [];

    /**
     * __constructor
     *
     * @access public
     *
     * @param array $config
     * @param array $allowMethod
     * @param array $lang
     */
    public function __construct($config = [], $allowMethod = null, $lang = [])
    {
        parent::__construct();

        $this->_config = array_merge($this->_config, $config);
        $this->_allowMethod = $allowMethod ?: $this->_allowMethod;
        $this->_lang = array_merge($this->_lang, $lang);

        $this->_setNeedValidateData();
    }

    /**
     * Set need validate data
     *
     * @access public
     * @return void
     */
    private function _setNeedValidateData()
    {
        if (!empty($this->_config['data'])) {
            $this->_data = $this->_config['data'];

            return;
        }

        $method = strtoupper($this->_config['method']);
        if (in_array($method, $this->_allowMethod)) {
            $data = [];
            switch ($method) {
                case 'GET' :
                    $data = $_GET;
                    break;

                case 'POST' :
                    $data = $_POST;
                    break;

                case 'GET_POST' :
                    $data = array_merge($_GET, $_POST);
                    break;
            }
            unset($data['r']);
            $this->_data = $data;
        }
    }

    /**
     * Run validate by rules
     *
     * @access public
     *
     * @param array $rules
     *
     * @return mixed
     */
    public function validate($rules)
    {
        if (!is_array($rules) || empty($rules)) {
            return $this->getError('rules_illegal');
        }

        foreach ($rules as $param => $rule) {
            $param = str_replace(' ', '', $param);
            $rule = str_replace(' ', '', $rule);
            $result = $this->_run($param, $rule);

            if (false === $result && $this->_config['return_error_first_time']) {
                return $this->getError();
            }
        }

        return $this->getError() ?: true;
    }

    /**
     * Validate param one by one
     *
     * @access private
     *
     * @param string $param
     * @param string $rule
     *
     * @return boolean
     */
    private function _run($param, $rule)
    {
        $rule = explode($this->_config['functions_split'], $rule);

        $field = Helper::underToCamel($param, false);
        if (false !== strpos($param, $this->_config['param_field_split'])) {
            list($param, $field) = explode($this->_config['param_field_split'], $param);
        }

        // required
        if (empty($this->_data[$param]) && in_array('required', $rule)) {
            $this->_setError('required' . self::SPRINTF_OFS . $field);

            if ($this->_config['return_error_first_time']) {
                return false;
            }
        }

        // default value
        if (empty($this->_data[$param]) && (false !== strpos(implode('', $rule), 'default_value'))) {
            $this->_data[$param] = '';
        }

        // params un exists
        if (!isset($this->_data[$param])) {
            if ($this->_config['set_unset_param_value_null']) {
                $this->_validatedData[$param] = $this->_data[$param] = null;
            }

            return true;
        }

        // must be array
        if (!is_array($this->_data[$param]) && in_array('is_array', $rule)) {
            $this->_setError('is_array' . self::SPRINTF_OFS . $field);

            if ($this->_config['return_error_first_time']) {
                return false;
            }
        }

        // unset someone
        foreach ($rule as $key => $val) {
            if (in_array($val, [
                'required',
                'is_array',
                'default_value'
            ])) {
                unset($rule[$key]);
            }
        }

        $result = $this->_exec($this->_data[$param], $rule, $field);

        if (true === $result) {
            $this->_validatedData[$param] = $this->_data[$param];
        }

        return $result;
    }

    /**
     * Execute validate
     *
     * @access private
     *
     * @param mixed  &$param
     * @param array  $rule
     * @param string $field
     *
     * @return mixed
     */
    private function _exec(&$param, $rule, $field)
    {
        if (is_array($param)) {
            foreach ($param as $val) {
                $result = $this->_exec($val, $rule, $field);

                if (false === $result && $this->_config['return_error_first_time']) {
                    return $this->getError();
                }
            }
        }

        // validate core
        foreach ($rule as $val) {

            // match function params
            preg_match('/(.*?)\\' . $this->_config['params_left_separator'] . '(.*)\\' . $this->_config['params_right_separator'] . '/', $val, $match);

            // params
            $_param = [
                $param
            ];
            if (!empty($match)) {
                $_param[] = explode($this->_config['params_split'], $match[2]);
            }

            // callback
            $call = null;
            if (false !== strpos($val, $this->_config['callback_function_prefix'])) {

                $call = [
                    new static(),
                    str_replace($this->_config['callback_function_prefix'], '', $val)
                ];

                $lang = $val . '_failed';
            } else {

                // this->method
                $lang = empty($match) ? $val : $match[1];

                $methodFn = '_' . Helper::underToCamel($lang);
                $fn = empty($match) ? $val : $match[1];

                if (method_exists($this, $methodFn)) {
                    $call = [
                        $this,
                        $methodFn
                    ];
                } else {
                    if (function_exists($fn)) {
                        $call = $fn;
                    } else {
                        if (empty($val)) {
                            return true;
                        } else {
                            $this->_setError('function_un_exists' . self::SPRINTF_OFS . $fn);

                            if ($this->_config['return_error_first_time']) {
                                return false;
                            }
                        }
                    }
                }
            }

            $result = call_user_func_array($call, $_param);

            if (!is_bool($result)) {
                $param = $result;
            } else {

                if (false === $result) {

                    $sprintfStr = $field;

                    if (!empty($_param[1])) {
                        foreach ($_param[1] as $value) {
                            $sprintfStr .= self::SPRINTF_OFS . $value;
                        }
                    }

                    $this->_setError($lang . self::SPRINTF_OFS . $sprintfStr);

                    if ($this->_config['return_error_first_time']) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Set error message
     *
     * @access private
     *
     * @param string $message
     *
     * @return void
     */
    private function _setError($message)
    {

        // has none params
        if (false === strpos($message, self::SPRINTF_OFS)) {
            $this->_error[] = $message;
        } else {
            $params = explode(self::SPRINTF_OFS, $message);
            $message = array_shift($params);

            if (empty($this->_lang[$message])) {
                $message = str_replace('_', ' ', $message);
            } else {
                $message = $this->_lang[$message];
            }

            $this->_error[] = sprintf($message, ...$params);
        }
    }

    /**
     * Get error message (set)
     *
     * @access public
     *
     * @param string $message
     *
     * @return mixed
     */
    public function getError($message = null)
    {
        if ($message) {
            $this->_setError($message);
        }

        if (empty($this->_error)) {
            return null;
        }

        // return first
        if ($this->_config['return_error_first_time']) {
            return current($this->_error);
        }

        // return all
        return implode(PHP_EOL, $this->_error);
    }

    /**
     * Get data
     *
     * @access public
     *
     * @param boolean $validated
     *
     * @return array
     */
    public function data($validated = true)
    {
        return $validated ? $this->_validatedData : $this->_data;
    }

    // ---

    /**
     * Trim
     *
     * @access protected
     *
     * @param string $str
     *
     * @return string
     */
    protected function _trim($str)
    {
        return trim($str);
    }

    /**
     * Min length
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $val
     *
     * @return boolean
     */
    protected function _minLength($str, $val)
    {
        if (preg_match("/[^0-9]/", $val[0])) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) < $val[0]) ? false : true;
        }

        return (strlen($str) < $val[0]) ? false : true;
    }

    /**
     * Between length
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $val
     *
     * @return boolean
     */
    protected function _betweenLength($str, $val)
    {
        list($min, $max) = $val;

        $minResult = $maxResult = true;

        // 验证下限
        if ($min) {
            $minResult = $this->_minLength($str, [
                $min
            ]);
        }

        // 验证上限
        if ($max) {
            $maxResult = $this->_maxLength($str, [
                $max
            ]);
        }

        return $minResult && $maxResult;
    }

    /**
     * Max length
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $val
     *
     * @return boolean
     */
    protected function _maxLength($str, $val)
    {
        if (preg_match("/[^0-9]/", $val[0])) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) > $val[0]) ? false : true;
        }

        return (strlen($str) > $val[0]) ? false : true;
    }

    /**
     * Exact length
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $val
     *
     * @return boolean
     */
    protected function _exactLength($str, $val)
    {
        if (preg_match("/[^0-9]/", $val[0])) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) == $val[0]) ? true : false;
        }

        return (strlen($str) == $val[0]) ? true : false;
    }

    /**
     * In array
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $val
     *
     * @return boolean
     */
    protected function _inArray($str, $val)
    {
        if (false !== strpos($val[0], '~')) {
            list($low, $high) = explode('~', $val[0]);
            $val = range($low, $high);
        }

        return in_array($str, $val);
    }

    /**
     * Valid email
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _email($str)
    {
        return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str) ? true : false;
    }

    /**
     * Alpha
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _alpha($str)
    {
        return preg_match("/^([a-z])+$/i", $str) ? true : false;
    }

    /**
     * Alpha-numeric
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _alphaNumeric($str)
    {
        return preg_match("/^([a-z0-9])+$/i", $str) ? true : false;
    }

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _alphaDash($str)
    {
        return preg_match("/^([-a-z0-9_-])+$/i", $str) ? true : false;
    }

    /**
     * Is numeric
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _numeric($str)
    {
        return is_numeric($str) ? true : false;
    }

    /**
     * Int value
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return integer
     */
    protected function _int($str)
    {
        return intval($str);
    }

    /**
     * Greater than
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $min
     *
     * @return boolean
     */
    protected function _gt($str, $min)
    {
        if (!is_numeric($str)) {
            return false;
        }

        return $str > $min[0];
    }

    /**
     * Greater than or equal
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $min
     *
     * @return boolean
     */
    protected function _gtEq($str, $min)
    {
        if (!is_numeric($str)) {
            return false;
        }

        return $str >= $min[0];
    }

    /**
     * Less than
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $max
     *
     * @return boolean
     */
    protected function _lt($str, $max)
    {
        if (!is_numeric($str)) {
            return false;
        }

        return $str < $max[0];
    }

    /**
     * Less than or equal
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $max
     *
     * @return boolean
     */
    protected function _ltEq($str, $max)
    {
        if (!is_numeric($str)) {
            return false;
        }

        return $str <= $max[0];
    }

    /**
     * Equal
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $equal
     *
     * @return boolean
     */
    protected function _eq($str, $equal)
    {
        if (!is_numeric($str)) {
            return false;
        }

        return $str == $equal[0];
    }

    /**
     * Between
     *
     * @access protected
     *
     * @param mixed $str
     * @param array $val
     *
     * @return boolean
     */
    protected function _between($str, $val)
    {
        list($min, $max) = $val;

        $greaterResult = $lessResult = true;

        // 验证下限
        if ($min) {
            $greaterResult = $this->_gtEq($str, [
                $min
            ]);
        }

        // 验证上限
        if ($max) {
            $lessResult = $this->_ltEq($str, [
                $max
            ]);
        }

        return $greaterResult && $lessResult;
    }

    /**
     * Is a natural number (0,1,2,3, etc.)
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _natural($str)
    {
        return (bool) preg_match('/^[0-9]+$/', $str);
    }

    /**
     * Is a natural number, but not a zero (1,2,3, etc.)
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _naturalNoZero($str)
    {
        if (!$this->_natural($str) || 0 == $str) {
            return false;
        }

        return true;
    }

    /**
     * Valid id card for china
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _idCard($str)
    {
        return (bool) preg_match('/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $str);
    }

    /**
     * Valid post code for china
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _postCode($str)
    {
        return (bool) preg_match('/^\d{6}$/', $str);
    }

    /**
     * Valid phone for china
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _phone($str)
    {
        return (bool) preg_match('/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|17[0-9]{9}$|18[0-9]{9}$/', $str);
    }

    /**
     * Valid qq for tencent
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _qq($str)
    {
        return (bool) preg_match('/^[1-9][0-9]{4,}$/', $str);
    }

    /**
     * Valid url
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _url($str)
    {
        return filter_var($str, FILTER_VALIDATE_URL);
    }

    /**
     * Valid chinese char
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _chinese($str)
    {
        return (bool) preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
    }

    /**
     * Valid special
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _specialExists($str)
    {
        return !(bool) preg_match('/[\/\`\-\=\[\]\;\\\'\\\,\.\/\~\!\@\#\$\%\^\&\*\(\)\_\+\{\}\:\"\|\<\>\?\·\【\】\；\’\、\，\。\、\！\￥\…\（\）\—\：\“\《\》\？\/]/', $str);
    }

    /**
     * Valid has html tags
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _htmlTagExists($str)
    {
        return ($str == strip_tags($str)) ? true : false;
    }

    /**
     * Valid json string
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _jsonString($str)
    {
        return is_null(json_decode($str)) ? false : true;
    }

    /**
     * Valid time string
     *
     * @access protected
     *
     * @param mixed $str
     *
     * @return boolean
     */
    protected function _timeString($str)
    {
        $result = strtotime($str);

        return ($result < 0 || $result === false) ? false : true;
    }

    /**
     * Valid range for mysql filed type of int data
     *
     * @access protected
     *
     * @param mixed  $str
     * @param string $type
     *
     * @return boolean
     */
    protected function _inMysqlIntSigned($str, $type)
    {
        if (!is_numeric($str) || !($range = $this->_getMysqlIntRange($type[0]))) {
            return false;
        }
        $range = ($range + 1) / 2;

        if ($str < -$range || $str > $range - 1) {
            return false;
        }

        return true;
    }

    /**
     * Valid range for mysql filed type of unsigned int data
     *
     * @access protected
     *
     * @param mixed  $str
     * @param string $type
     *
     * @return boolean
     */
    protected function _inMysqlInt($str, $type)
    {
        if (!is_numeric($str) || !($range = $this->_getMysqlIntRange($type[0]))) {
            return false;
        }

        if ($str < 0 || $str > $range) {
            return false;
        }

        return true;
    }

    /**
     * Get range of mysql int type
     *
     * @access protected
     *
     * @param string $type
     *
     * @return null | integer
     */
    private function _getMysqlIntRange($type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case 'TINYINT' :
                $max = 255;
                break;
            case 'SMALLINT' :
                $max = 65535;
                break;
            case 'MEDIUMINT' :
                $max = 16777215;
                break;
            case 'INT' :
            case 'INTEGER' :
                $max = 4294967295;
                break;
            case 'BIGINT' :
                $max = 18446744073709551615;
                break;
            default :
                $max = null;
        }

        return $max;
    }

    /**
     * Set default value
     *
     * @access protected
     *
     * @param mixed $str
     * @param mixed $default
     *
     * @return mixed
     */
    protected function _defaultValue($str, $default)
    {
        return empty($str) ? $default[0] : $str;
    }
}
