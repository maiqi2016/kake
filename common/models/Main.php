<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii;
use Oil\src\Helper;

/**
 * Main model
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-11-18 09:18:45
 */
class Main extends ActiveRecord
{
    /**
     * @var array 对应 upload rules key 的描述
     */
    public $_upload_rules = [
        'mimes' => 'MIME类型',
        'suffix' => '文件后缀',
        'pic_sizes' => '图片尺寸(PX)',
        'max_size' => '文件不超过(KB)',
    ];

    /**
     * @var string table name
     */
    public $tableName;

    /**
     * @var array model instance
     */
    public static $model;

    /**
     * @var boolean use cache
     */
    public $useCache;

    /**
     * Constructor
     *
     * @param string  $name
     * @param boolean $useCache
     * @param array   $config
     */
    public function __construct($name = null, $useCache = false, $config = null)
    {
        $this->tableName = Helper::camelToUnder($name);
        $this->useCache = $useCache;
        parent::__construct($config ?: []);

        $model = ($this->tableName ? Helper::underToCamel($this->tableName, false) : 'Main');
        Yii::info('实例化模型: ' . $model . 'Model');
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $meta = $this->meta($this->useCache);

        return empty($meta['fnRules']) ? [] : $meta['fnRules'];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        $meta = $this->meta($this->useCache);

        return empty($meta['fnAttributeLabels']) ? [] : $meta['fnAttributeLabels'];
    }

    /**
     * Call service
     *
     * @access public
     *
     * @param string $api
     * @param array  $params
     * @param string $cache
     * @param string $project
     * @param string $lang
     *
     * @return mixed
     * @throws \Exception
     */
    public function service($api, $params = [], $cache = 'no', $project = PROJECT, $lang = 'zh-CN')
    {
        $conf = Yii::$app->params;

        // array to string
        array_walk($params, function (&$value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } else if (is_numeric($value)) {
                $value = (string) $value;
            } else if (is_bool($value)) {
                $value = (string) ($value ? 1 : 0);
            } else if (!is_string($value)) {
                $value = null;
            }
        });

        // merge params
        $api = $project . '.' . $api;
        $params = array_merge($params, [
            'app_api' => $api,
            'app_id' => $conf['service_app_id'],
            'app_secret' => $conf['service_app_secret'],
            'app_lang' => $lang,
            'app_cache' => $cache
        ]);

        // create sign
        unset($params['r']);
        $params = Helper::createSign($params);
        $params = '"' . http_build_query($params) . '"';

        // call client
        $client = realpath(Yii::getAlias('@thrift/client.php'));
        Yii::info('服务请求开始: ' . $api . ' with ' . json_encode($params));
        $cmd = Helper::joinString(' ', 'php', $client, $params, $conf['thrift_ip'], $conf['thrift_port']);
        exec($cmd, $result);
        Yii::info('服务请求结束');

        $result = Helper::handleCliResult($result);

        if ($result['state'] == -1) {
            if (empty($result['info'])) {
                $result['info'] = '接口未返回任何数据';
            }
            Yii::error($result['info']);
            if (strpos($result['info'], '<!doctype html>') === false) {
                throw new \Exception($result['info']);
            }
            exit($result['info']);
        }

        if ($result['info'] == 'DEBUG') {
            $this->dump($result['data']);
        }

        return $result['state'] ? $result['data'] : $result['info'];
    }

    /**
     * Dump variable
     *
     * @param mixed $var
     * @param bool  $strict
     * @param bool  $exit
     *
     * @return void
     */
    public function dump($var, $strict = false, $exit = true)
    {
        Helper::dump($var, $exit, $strict);
    }

    /**
     * 获取缓存
     *
     * @param mixed                   $key
     * @param callable                $fetchFn
     * @param int                     $time
     * @param \yii\caching\Dependency $dependent
     * @param boolean                 $useCache
     *
     * @return mixed
     */
    public function cache($key, $fetchFn, $time = null, $dependent = null, $useCache = true)
    {
        if (!$useCache || Yii::$app->session->getFlash('no_cache')) {
            return call_user_func($fetchFn);
        }

        if (!is_string($key)) {
            $key = strtolower(static::className()) . '_' . md5(json_encode($key));
        }

        $cache = Yii::$app->cache;
        $key = strtolower(Yii::$app->id) . '_' . $key;

        Yii::info('开始从 ' . $cache::className() . ' 中获取缓存');
        $data = $cache->get($key);

        if (false === $data) {
            Yii::info('缓存命中失败并重新获取写入: ' . $key);
            $data = call_user_func($fetchFn);
            $time = isset($time) ? $time : DAY;
            $result = $cache->set($key, $data, $time, $dependent);

            if ($result === false) {
                Yii::error('写入缓存失败: ' . $key);
            }
        } else {
            Yii::info('缓存命中成功: ' . $key);
        }

        return $data;
    }

    /**
     * Get meta data of model
     *
     * @access public
     *
     * @param boolean $useCache
     *
     * @return array
     */
    public function meta($useCache = true)
    {
        if (empty($this->tableName)) {
            return [];
        }

        if (isset(self::$model[$this->tableName]) && !empty(self::$model[$this->tableName])) {
            return self::$model[$this->tableName];
        }

        self::$model[$this->tableName] = $this->cache('general.' . $this->tableName . '.model.meta.', function () {
            Yii::info('获取模型表原数据: ' . $this->tableName);

            return $this->service('general.model-meta', [
                'table' => $this->tableName
            ], 'no');
        }, YEAR, null, $useCache);

        return self::$model[$this->tableName];
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        $meta = $this->meta($this->useCache);
        if (isset($meta[$name])) {
            return $meta[$name];
        }

        return parent::__get($name);
    }
}
