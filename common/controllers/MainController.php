<?php

namespace common\controllers;

use common\models\Main;
use Symfony\Component\HttpFoundation\Response;
use yii;
use yii\web\Controller;
use Oil\src\Helper;
use yii\base\DynamicModel;
use yii\helpers\Url;
use yii\helpers\Html;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Intervention\Image\ImageManagerStatic as Image;
use yii\helpers\ArrayHelper;

/**
 * Main controller
 * @method mixed service($api, $params = [], $cache = 'no', $project = PROJECT, $lang = 'zh-CN')
 * @method mixed dump($var, $strict = false, $exit = true)
 * @method mixed cache($key, $fetchFn, $time = null, $dependent = null, $useCache = true)
 */
class MainController extends Controller
{
    /**
     * @var object 用户信息对象
     */
    protected $user;

    /**
     * @var mixed 前端 CSS 资源
     * @example false, null/auto
     */
    public $sourceCss = false;

    /**
     * @var mixed 前端 JS 资源
     * @example false, null/auto
     */
    public $sourceJs = false;

    /**
     * @var string 通用列表接口
     */
    public static $apiList = 'general.list';

    /**
     * @var string 通用详情接口
     */
    public static $apiDetail = 'general.detail';

    /**
     * @var string 通用新增接口
     */
    public static $apiNewly = 'general.newly';

    /**
     * @var string 通用编辑接口
     */
    public static $apiEdit = 'general.edit';

    /**
     * @var string 通用存在则更新否则新增接口
     */
    public static $apiNewlyOrEdit = 'general.newly-or-edit';

    /**
     * @cont string language
     */
    const LANGUAGE = 'language';

    /**
     * @inheritdoc
     */
    public function init()
    {
        Helper::executeOnce(
            function () {
                parent::init();

                Yii::info('开始读取配置表中的配置');
                $config = $this->cache(
                    'list.app.config.kvp',
                    function () {
                        return $this->service('general.config-kvp');
                    },
                    DAY,
                    null,
                    Yii::$app->params['use_cache']
                );

                Yii::$app->params = array_merge($config['file'], Yii::$app->params, $config['db']);

                $this->oilExtendParams();
            }
        );
    }

    /**
     * 通用方法
     *
     * @access public
     * @return array
     */
    public function actions()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $except = [
            'general/ajax-upload',
            'general/ajax-ck-editor-upload',
        ];
        if (!in_array($action->controller->id . '/' . $action->id, $except)) {
            if (strpos($action->id, 'ajax-') === 0) {
                $this->mustAjax();
            }
        }

        if ($callback = Yii::$app->request->get('callback')) {
            if (!in_array($action->id, ['error'])) {
                $this->logReference('callback', $callback);
            }
        } else {
            $this->goReference('callback', null, null, false);
        }

        return parent::beforeAction($action);
    }

    /**
     * 完全自定义组件扩展参数
     *
     * @access public
     * @return void
     */
    public function oilExtendParams()
    {
        $oil = Yii::$app->oil;

        $extendParams = [
            'upload' => ['config' => ['root_path' => Yii::$app->params['tmp_path']]],
            'wx'     => ['config' => ['oauth' => ['callback' => SCHEME . Yii::$app->params['wechat_callback']]]],
            'ali'    => ['config' => ['options' => ['callback' => SCHEME . Yii::$app->params['alipay_callback']]]],
            'oss'    => ['config' => ['host' => SCHEME . Yii::$app->params['upload_url']]],
            'sso'    => ['config' => ['host' => SCHEME . Yii::$app->params['passport_url']]],
        ];

        $oil->oil = ArrayHelper::merge($oil->oil, $extendParams);
    }

    /**
     * 跨命名空间调用控制器方法
     *
     * @access public
     *
     * @param string  $controller
     * @param string  $namespace
     * @param boolean $new
     *
     * @return mixed
     */
    public function controller($controller, $namespace = 'backend', $new = true)
    {
        static $pool = [];

        if (!strpos($controller, 'Controller')) {
            $controller = Helper::underToCamel($controller, false, '-') . 'Controller';
        }

        $key = md5($controller);
        if (!isset($pool[$key])) {
            $class = '\\' . $namespace . '\controllers\\' . $controller;
            if (!$new) {
                $pool[$key] = $class;
            } else {
                $pool[$key] = Helper::singleton(
                    $class,
                    function () use ($class) {
                        return new $class($this->id, $this->module);
                    }
                );
            }
        }

        return $pool[$key];
    }

    /**
     * 获取模型
     *
     * @access public
     *
     * @param string $model
     * @param array  $config
     *
     * @return Main
     */
    public static function model($model = null, $config = [])
    {
        static $pool = [];

        $key = md5($model);
        if (!isset($pool[$key])) {
            $pool[$key] = Helper::singleton(
                $model,
                function () use ($model, $config) {
                    return new Main($model, Yii::$app->params['use_cache'], $config);
                }
            );
        }

        return $pool[$key];
    }

    /**
     * SEO
     *
     * @param array $params
     *
     * @return void
     */
    protected function seo($params)
    {
        foreach ($params as $key => $value) {
            Yii::$app->params[$key] = $value;
        }
    }

    /**
     * 显示正确提示页面
     *
     * @access public
     *
     * @param string $message
     * @param string $title
     * @param string $extraHtml
     *
     * @return void
     */
    public function message($message, $title = null, $extraHtml = null)
    {
        $this->sourceCss = [
            'message/index',
        ];

        if (is_array($message)) {
            $message = $this->messageParseLink($message);
        }

        $params = [
            'type'    => 'message',
            'message' => $message,
            'title'   => $title,
            'extra'   => $extraHtml,
        ];

        $this->seo(['title' => $title]);
        $content = $this->renderFile(Yii::$app->getViewPath() . DS . 'message.php', $params);
        $content = $this->renderContent($content);

        exit($content);
    }

    /**
     * 解析提示数据中的链接
     *
     * @access public
     *
     * @param array  $message
     * @param string $package
     *
     * @return string
     */
    public function messageParseLink($message, $package = 'common')
    {
        foreach ($message as &$item) {
            if (!is_array($item)) {
                continue;
            }
            $options = isset($item['options']) ? $item['options'] : [];
            $item = Html::a($item['text'], $item['router'], $options);
        }

        return $this->lang($message, $package);
    }

    /**
     * 语言包翻译 - 支持多个语言包
     *
     * @access public
     *
     * @param mixed  $lang
     * @param string $package
     *
     * @return string
     */
    public function lang($lang, $package = 'common')
    {
        if (is_string($lang)) {
            return Yii::t($package, $lang);
        }

        if (!is_array($lang)) {
            return null;
        }

        if (is_array(current($lang))) {
            $text = null;
            foreach ($lang as $_lang) {
                $text .= $this->lang($_lang, $package);
            }

            return $text;
        }

        $params = $lang;
        $lang = array_shift($params);

        return Yii::t($package, $lang, $params);
    }

    /**
     * 前后端交互 API 返回请求结果
     *
     * @access public
     *
     * @param int    $state   返回的操作状态 1-成功 0-失败
     * @param string $info    返回的提示信息
     * @param mixed  $data    返回数据
     * @param string $type    返回类型
     * @param string $jsonPFn jsonP类型时的执行函数
     *
     * @return void
     */
    public function json($state, $info = null, $data = null, $type = 'JSON', $jsonPFn = null)
    {
        $result = [
            'state' => $state,
            'info'  => $info,
            'data'  => $data,
        ];

        switch (strtoupper($type)) {
            case 'TEXT' :
                $type = 'text/html';
                break;

            case 'JSON-P' :
                $type = 'application/json';
                $result = $jsonPFn . '(' . json_encode($result, JSON_UNESCAPED_UNICODE) . ');';
                break;

            default :
                $type = 'application/json';
        }

        header('Content-Type:' . $type . '; charset=utf-8');
        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 返回成功提示信息及数据
     *
     * @access public
     *
     * @param mixed  $data    返回数据
     * @param mixed  $lang    成功提示信息
     * @param string $package 语言包
     *
     * @return void
     */
    public function success($data = [], $lang = null, $package = 'common')
    {
        $info = $this->lang($lang, $package);
        Yii::info($info);

        $this->json(1, $info, $data);
    }

    /**
     * 返回失败提示信息
     *
     * @access public
     *
     * @param mixed  $lang    成功提示信息
     * @param string $package 语言包
     *
     * @return void
     */
    public function fail($lang, $package = 'common')
    {
        $info = $this->lang($lang, $package);
        Yii::info($info);

        $this->json(0, $info, null);
    }

    /**
     * 必须为 Ajax 操作的前置判断
     *
     * @access protected
     * @return boolean
     */
    protected function mustAjax()
    {
        if (Yii::$app->request->isAjax) {
            return true;
        }

        $this->error(Yii::t('common', 'support ajax method only'), 403);

        return false;
    }

    /**
     * 验证数据
     *
     * @access public
     *
     * @param array   $params
     * @param array   $rules
     * @param boolean $response
     *
     * @return mixed
     */
    public function validate($params, $rules, $response = true)
    {
        $model = DynamicModel::validateData($params, $rules);

        if ($model->hasErrors()) {

            $error = current($model->getFirstErrors());

            if (!$response) {
                return $error;
            }

            if (Yii::$app->request->isAjax) {
                $this->fail($error);
            } else {
                $this->error($error);
            }
        }

        return true;
    }

    /**
     * 上传
     *
     * @access protected
     *
     * @param array   $config
     * @param mixed   $cropData
     * @param boolean $ajaxMode
     *
     * @return mixed
     */
    protected function uploader($config = [], $cropData = null, $ajaxMode = true)
    {
        Yii::$app->oil->register('upload', ['config' => $config]);
        $uploader = Yii::$app->oil->upload;

        // 上传到本地服务器
        $result = $uploader->upload($_FILES);
        if (is_string($result)) {
            if (!$ajaxMode) {
                return $result;
            }
            $this->fail($result);
        }

        $file = current($result);

        // 记录上传日志
        $result = $this->service(
            'general.add-for-backend',
            [
                'table'     => 'attachment',
                'deep_path' => $file['save_path'],
                'filename'  => $file['save_name'],
            ]
        );

        $attachmentId = $result['id'];

        if (is_string($result)) {
            @unlink($file['file']);
            if (!$ajaxMode) {
                return $result;
            }
            $this->fail($result);
        }

        // 上传到阿里云 OSS
        $result = Yii::$app->oil->oss->upload($file['file']);
        if (is_string($result)) {
            if (!$ajaxMode) {
                return $result;
            }
            $this->fail($result);
        }

        // 返回数据
        $url = Yii::$app->params['upload_url'];
        $result = [
            'name'   => $file['name'],
            'id'     => $attachmentId,
            'width'  => $file['width'],
            'height' => $file['height'],
            'url'    => $url . '/' . $file['save_path'] . '-' . $file['save_name'],
        ];

        if ($cropData && !empty($cropData['width']) && !empty($cropData['height'])) {
            if ($cropData['width'] != $file['width'] || $cropData['height'] != $file['height']) {
                $result['crop'] = $cropData;
            }
        }

        if (!$ajaxMode) {
            $result['deep_path'] = $file['save_path'];
            $result['filename'] = $file['save_name'];

            return $result;
        }

        $this->success($result);

        return true;
    }

    /**
     * 获取 controller 名称
     *
     * @access public
     *
     * @param string $action
     * @param string $split
     *
     * @return string
     */
    public function getControllerName($action = null, $split = '/')
    {
        $controller = Helper::cutString(
            static::className(),
            [
                '\^0^desc',
                'Controller^0',
            ]
        );

        $controller = Helper::camelToUnder($controller, '-');
        if (empty($action)) {
            return $controller;
        }

        $action = Helper::camelToUnder($action, '-');

        return $controller . $split . $action;
    }

    /**
     * 合成附件URL
     *
     * @access public
     *
     * @param array  $record
     * @param mixed  $items
     * @param string $suffix
     * @param string $separator
     *
     * @return array
     */
    public function createAttachmentUrl($record, $items, $suffix = 'preview_url', $separator = '-')
    {
        $items = (array)$items;
        foreach ($items as $attachmentIdKey => $preKey) {

            if (is_numeric($attachmentIdKey)) {
                $attachmentIdKey = $preKey;
                $preKey = null;
            }

            $prefixTag = empty($preKey) ? null : $preKey . '_';
            $deepPath = $prefixTag . 'deep_path';
            $filename = $prefixTag . 'filename';

            if (empty($record[$deepPath]) || empty($record[$filename])) {
                continue;
            }

            $url = Yii::$app->params['upload_url'];
            $id = $record[$attachmentIdKey];

            $_value = $url . DS . $record[$deepPath] . $separator . $record[$filename];
            $_value = $this->getCompressPicUrl($_value);

            $record[$prefixTag . $suffix] = [
                $id => $_value,
            ];
        }

        return $record;
    }

    /**
     * @param $url
     *
     * @return string
     */
    public function getCompressPicUrl($url)
    {
        $suffix = '?x-oss-process=style/compress_product';
        if (strpos($url, $suffix) !== false) {
            return $url;
        }

        $ext = Helper::getSuffix($url);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $url .= '?x-oss-process=style/compress_product';
        }

        return $url;
    }

    /**
     * 合成附件URL - 多附件情况
     *
     * @access public
     *
     * @param array  $record
     * @param array  $items
     * @param string $suffix
     *
     * @return array
     */
    public function createAttachmentUrls($record, $items, $suffix = 'preview_url')
    {
        foreach ($items as $attachmentIdsKey => $tagKey) {
            if (empty($record[$attachmentIdsKey])) {
                continue;
            }

            $attachment = $this->service(
                'general.list-attachment-by-ids',
                [
                    'ids' => $record[$attachmentIdsKey],
                ]
            );
            $attachment = Helper::arraySortAppointIndex($attachment, 'id', $record[$attachmentIdsKey]);

            foreach ($attachment as &$item) {
                $item = $this->createAttachmentUrl($item, 'id');
            }

            $tagKey = empty($tagKey) ? null : $tagKey . '_';

            foreach (array_column($attachment, $suffix) as $value) {
                $_value = current($value);
                $_value = $this->getCompressPicUrl($_value);
                $record[$tagKey . $suffix][key($value)] = $_value;
            }
        }

        return $record;
    }

    /**
     * 根据给定的值生成兼容的 url
     *
     * @access public
     *
     * @param mixed  $item
     * @param string $param
     *
     * @return string
     */
    public function compatibleUrl($item, $param = 'frontend_url')
    {
        $item = (array)$item;
        $str = $item[0];
        $str = !empty($str) ? $str : 'javascript:void(null);';

        if (strpos($str, 'http') === 0 || strpos($str, '//') === 0) {
            return $str;
        }

        if (strpos($str, 'javascript:') === 0) {
            return $str;
        }

        if (strpos($str, '?')) {
            $query = parse_url($str, PHP_URL_QUERY);
            parse_str($query, $query);

            $item[0] = '/' . trim(explode('?', $str)[0], '/');
            $item = array_merge($item, $query);
        }

        if ($param && isset(Yii::$app->params[$param])) {
            return Yii::$app->params[$param] . Url::toRoute($item);
        }

        return Url::toRoute($item);
    }

    /**
     * 生成链接 URL
     *
     * @access public
     *
     * @param array    $record
     * @param mixed    $items
     * @param callable $preHandler
     * @param string   $suffix
     * @param string   $param
     *
     * @return array
     */
    public function createLinkUrl($record, $items, $preHandler = null, $suffix = 'link_url', $param = 'frontend_url')
    {
        $items = (array)$items;
        foreach ($items as $oldKey => $newKey) {
            if (is_numeric($oldKey)) {
                $oldKey = $newKey;
                $newKey = null;
            }

            $preKey = empty($newKey) ? null : $newKey . '_';
            if (isset($record[$oldKey])) {
                if (is_callable($preHandler)) {
                    $item = call_user_func($preHandler, $record[$oldKey]);
                } else {
                    $item = $record[$oldKey];
                }
                $record[$preKey . $suffix] = $this->compatibleUrl($item, $param);
            }
        }

        return $record;
    }

    /**
     * 列表逻辑外键的数据
     *
     * @access public
     *
     * @param array    $record
     * @param mixed    $items
     * @param callable $extraHandler
     * @param string   $action
     *
     * @return array
     */
    public function listForeignData($record, $items, $extraHandler = null, $action = null)
    {
        $items = (array)$items;
        $action = $action ?: 'edit';

        $assists = $this->callStatic($action . 'Assist', null, [$action]);

        foreach ($items as $key) {
            if (!isset($assists[$key])) {
                continue;
            }
            $assist = $assists[$key];

            $api = isset($assist['service_api']) ? $assist['service_api'] : null;
            $params = isset($assist['service_params']) ? $assist['service_params'] : [];
            $params = array_merge($params, [$assist['foreign_key'] => $record['id']]);

            $record[$key] = $this->service($api, $params);

            if (isset($assist['handler_controller'])) {
                $controller = $assist['handler_controller'];
            } else {
                $controller = str_replace('_', '-', $assist['table']);
                $controller = $this->controller($controller, 'backend', false);
            }

            $extraData = [];
            if ($extraHandler && $result = call_user_func($extraHandler, $record)) {
                if (is_array($result)) {
                    $extraData = $result;
                }
            }

            foreach ($record[$key] as $k => $v) {
                $v = array_merge($v, $extraData);
                $record[$key][$k] = $this->callMethod('sufHandleField', $v, [$v], $controller);
            }

            if (isset($assist['field_name'])) {
                $record[$assist['field_name']] = implode(',', array_column($record[$key], 'id'));
            }
        }

        return $record;
    }

    /**
     * 获取字段对应的描述信息
     *
     * @param array   $record
     * @param mixed   $field
     * @param boolean $forceEmpty
     *
     * @return array
     */
    public function getFieldInfo($record, $field, $forceEmpty = false)
    {
        $field = (array)$field;
        foreach ($field as $item) {
            $key = '_' . $item;
            if (!isset($record[$item])) {
                continue;
            }

            $keys = [
                $key,
                $key . '_except',
            ];
            foreach ($keys as $attr) {
                if (!isset(static::${$attr})) {
                    continue;
                }

                $value = static::${$attr};
                if ($forceEmpty) {
                    $key = empty($record[$item]) ? 0 : 1;
                } else {
                    $key = $record[$item];
                }
                if (isset($value[$key])) {
                    $record[$item . '_info'] = $value[$key];
                }
            }
        }

        return $record;
    }

    /**
     * 当前请求的 URL
     *
     * @access public
     * @return string
     */
    public function currentUrl()
    {
        return Yii::$app->request->getHostInfo() . Yii::$app->request->url;
    }

    /**
     * Reference logger
     *
     * @access public
     *
     * @param string $key
     * @param string $url
     *
     * @return void
     */
    public function logReference($key, $url = null)
    {
        if (Yii::$app->request->isAjax) {
            return null;
        }

        $reference = Yii::$app->session->get(static::REFERENCE);
        if (empty($reference)) {
            $reference = [$key => Yii::$app->request->referrer];
        }

        $reference[$key] = Helper::unsetParamsForUrl('callback', $url ?: $this->currentUrl());
        Yii::$app->session->set(static::REFERENCE, $reference);
    }

    /**
     * Go to the reference
     *
     * @access public
     *
     * @param string  $key
     * @param array   $flash
     * @param array   $params
     * @param boolean $auto
     *
     * @return void
     */
    public function goReference($key, $flash = [], $params = [], $auto = true)
    {
        if (Yii::$app->request->isAjax) {
            return;
        }

        $reference = Yii::$app->session->get(static::REFERENCE);
        if (empty($reference) || empty($reference[$key])) {
            if (!$auto) {
                return;
            }
            $url = Yii::$app->params[Yii::$app->id . '_url'];
        } else {
            $url = $reference[$key];
            if (!empty($params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query((array)$params);
            }

            unset($reference[$key]);
            Yii::$app->session->set(static::REFERENCE, $reference);
        }

        foreach ((array)$flash as $key => $msg) {
            Yii::$app->session->setFlash($key, $msg);
        }

        header('Location: ' . $url);
        exit();
    }

    /**
     * 获取引用地址
     *
     * @return mixed
     */
    public function reference()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    /**
     * 获取上一个调用者的方法名
     *
     * @access public
     *
     * @param integer $index
     *
     * @return string
     */
    public function getCaller($index = 1)
    {
        $caller = Helper::functionCallTrance($index);
        $caller = (strpos($caller, 'action') === 0 ? $caller : __FUNCTION__);

        $caller = lcfirst(str_replace('action', '', $caller));

        return $caller;
    }

    /**
     * Call static at around $this class
     *
     * @access public
     *
     * @param string $method
     * @param mixed  $default
     * @param mixed  $params
     * @param mixed  $class
     *
     * @return mixed
     */
    public function callStatic($method, $default = null, $params = null, $class = null)
    {
        $method = ucfirst($method);

        $class = $class ?: get_called_class();
        if (!method_exists($class, $method)) { // include parent class
            return $default;
        }

        $params = (array)$params;

        return $class::$method(...$params);
    }

    /**
     * Call method at around $this class
     *
     * @access public
     *
     * @param string $method
     * @param mixed  $default
     * @param mixed  $params
     * @param mixed  $class
     *
     * @return mixed
     */
    public function callMethod($method, $default = null, $params = null, $class = null)
    {
        if (isset($class)) {
            $class = is_object($class) ? $class : (new $class($this->id, $this->module));
        } else {
            $class = $this;
        }

        if (!method_exists($class, $method)) {
            return $default;
        }
        $params = (array)$params;

        return $class->$method(...$params);
    }

    /**
     * 调用接口 (简单版)
     *
     * @param string $appName
     * @param string $api
     * @param array  $params
     *
     * @return array
     */
    public function api($appName, $api, $params = [])
    {
        if (!in_array(
            $appName,
            [
                'backend',
                'frontend',
            ]
        )
        ) {
            return [
                'state' => 0,
                'info'  => 'Unknown application name.',
            ];
        }

        $api = str_replace('.', '/', $api);
        $url = SCHEME . Yii::$app->params[$appName . '_url'] . Url::toRoute([$api]);

        $params = array_merge(
            $params,
            [
                'api_token' => strrev(md5(Yii::$app->params['api_token_' . $appName])),
                'api_app'   => $appName,
            ]
        );

        $result = Helper::cURL($url, 'GET', $params);
        $result = Helper::handleCurlResult($result);

        return $result;
    }

    /**
     * 分发接口 (简单版)
     *
     * @param callable $callback
     *
     * @return void
     */
    public function ipa($callback)
    {
        $params = Yii::$app->request->get();

        $token = Helper::issetDefault($params, 'api_token');
        $appName = Helper::issetDefault($params, 'api_app');
        $_token = Helper::issetDefault(Yii::$app->params, 'api_token_' . $appName);
        $_token = strrev(md5($_token));

        if (!$token || $token != $_token) {
            $this->fail(
                [
                    'param illegal',
                    'param' => 'api token',
                ]
            );
        }
        unset($params['api_token'], $params['api_app']);

        $result = call_user_func($callback, $params);
        if (isset($result['state']) && $result['state'] < 1) {
            $this->fail($result['info']);
        }

        $this->success($result);
    }

    /**
     * 获取分销商用户信息
     *
     * @access public
     *
     * @param integer $userId
     *
     * @return array
     */
    public function getProducer($userId)
    {
        $controller = $this->controller('producer-setting');
        $condition = $this->callMethod('indexCondition', [], null, $controller);
        $condition = array_merge(
            $condition,
            [
                'table' => 'producer_setting',
                'where' => [
                    ['producer_setting.producer_id' => $userId],
                    ['producer_setting.state' => 1],
                ],
            ]
        );

        $producer = $this->service(self::$apiDetail, $condition);
        if (empty($producer)) {
            return [];
        }

        $producer = $this->callMethod(
            'sufHandleField',
            $producer,
            [
                $producer,
                'list',
            ],
            $controller
        );

        return $producer;
    }

    /**
     * 生成二维码图片
     *
     * @param string  $content
     * @param integer $qrWidth
     * @param string  $logo
     * @param integer $logoWidth
     *
     * @return QrCode
     */
    public function createQrCode($content, $qrWidth = 300, $logo = null, $logoWidth = null)
    {
        $qrCode = new QrCode($content);
        $qrCode->setSize($qrWidth);

        $qrCode->setWriterByName('png');
        $qrCode->setMargin($qrWidth / 25);
        $qrCode->setEncoding('utf-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::QUARTILE);
        $qrCode->setForegroundColor(
            [
                'r' => 0,
                'g' => 0,
                'b' => 0,
            ]
        );
        $qrCode->setBackgroundColor(
            [
                'r' => 255,
                'g' => 255,
                'b' => 255,
            ]
        );

        if ($logo) {
            $this->handleQrLogo($logo);
            $qrCode->setLogoPath($logo);
            $logoWidth = $logoWidth ?: $qrWidth / 4;
            $qrCode->setLogoWidth($logoWidth);
        }
        $qrCode->setValidateResult(false);

        return $qrCode;
    }

    /**
     * 通过 url 获取临时文件路径
     *
     * @access public
     *
     * @param string $url
     * @param string $host
     * @param string $name
     *
     * @return bool|string
     */
    public static function getPathByUrl($url, $host = null, $name = null)
    {
        if (strpos($url, '//') === 0) {
            $url = SCHEME . $url;
        }

        if (strpos($url, 'http') !== 0 && !empty($host)) {
            $url = SCHEME . Yii::$app->params[$host] . '/' . $url;
        }

        $file = Yii::$app->params['tmp_path'] . DS . ($name ?: basename($url));

        return Helper::saveRemoteFile($url, $file) ? $file : false;
    }

    /**
     * 通过文件路获取可访问的 url
     *
     * @access public
     *
     * @param string $file
     * @param string $ext
     * @param string $separator
     * @param string $prefix
     *
     * @return bool|string
     */
    public static function getUrlByPath($file, $ext = 'jpg', $separator = '-', $prefix = null)
    {
        $path = Helper::createFilePath(Yii::$app->params['tmp_path'], $ext, $separator, $prefix);
        $result = Yii::$app->oil->oss->upload($file, $path['deep'] . '-' . $path['filename']);
        if (is_string($result)) {
            @unlink($file);

            return false;
        }

        $url = Yii::$app->params['upload_url'] . '/' . $path['deep'] . $separator . $path['filename'];

        return $url;
    }

    /**
     * Handle file for Qr picture
     *
     * @access public
     *
     * @param &$path
     *
     * @return void
     */
    public function handleQrLogo(&$path)
    {
        $logoBg = Image::make(self::getPathByUrl('img/qr-code-logo-bg.png', 'frontend_source'));
        $logo = Image::make($path)->resize(94, 94);
        $logoBg->insert($logo, 'center');

        $path = str_replace('.jpg', '.png', $path);
        $logoBg->save($path);
    }

    /**
     * Debug
     *
     * @param callable $callback
     * @param int      $userId
     *
     * @return mixed
     */
    public function debug($callback, $userId = 1)
    {
        if (!$this->user) {
            return null;
        }

        if ($this->user->id != $userId) {
            return false;
        }

        $result = call_user_func($callback);
        if ($result === null) {
            return false;
        }
        $this->dump($result, false, true);

        return true;
    }

    /**
     * 生成缩略图 (保留原图，可能出现留白)
     *
     * @param string  $imgFile
     * @param integer $width
     * @param integer $height
     * @param string  $bgColor
     * @param boolean $cover
     * @param string  $separator
     *
     * @return mixed
     */
    public function thumbOriginal($imgFile, $width, $height, $bgColor = null, $cover = false, $separator = '-')
    {
        $img = Image::make($imgFile);
        $result = Helper::calThumb($width, $height, $img->width(), $img->height());
        $img->resize($result['width'], $result['height']);

        $bg = Image::canvas($width, $height, $bgColor);
        $bg->insert($img, 'top-left', intval($result['left']), intval($result['top']));

        if ($cover) {
            $bg->save($imgFile);

            return true;
        }

        $suffix = Helper::getSuffix($imgFile);
        $path = Helper::createFilePath(
            Yii::$app->params['tmp_path'],
            $bgColor ? $suffix : 'png',
            $separator,
            'thumb_original_'
        );
        $bg->save($path['file']);

        return $path;
    }

    /**
     * 生成缩略图 (裁切原图，可能缩略后不人性化)
     *
     * @param string  $imgFile
     * @param integer $width
     * @param integer $height
     * @param boolean $cover
     * @param string  $separator
     *
     * @return mixed
     */
    public function thumbCrop($imgFile, $width, $height, $cover = false, $separator = '-')
    {
        $img = Image::make($imgFile);
        $img->fit(
            $width,
            $height,
            function ($constraint) {
                $constraint->upsize();
            }
        );

        if ($cover) {
            $img->save($imgFile);

            return true;
        }

        $suffix = Helper::getSuffix($imgFile);
        $path = Helper::createFilePath(Yii::$app->params['tmp_path'], $suffix, $separator, 'thumb_crop_');
        $img->save($path['file']);

        return $path;
    }

    /**
     * 获取根用户
     *
     * @access public
     *
     * @param string $firstKey
     * @param string $secondKey
     *
     * @return array
     */
    public function getRootUsers($firstKey = 'private', $secondKey = 'root_user_ids')
    {
        if (empty(Yii::$app->params[$firstKey])) {
            return [];
        }

        if (empty(Yii::$app->params[$firstKey][$secondKey])) {
            return [];
        }

        $user = Yii::$app->params[$firstKey][$secondKey];
        if (is_array($user)) {
            return $user;
        }

        $user = Helper::handleString($user);

        return $user;
    }

    /**
     * 列表用户
     *
     * @access public
     *
     * @param array  $where
     * @param string $get_field
     * @param array  $user_ids
     *
     * @return array
     */
    public function listUser($where = [], $get_field = 'username', $user_ids = null)
    {
        $user = $this->cache(
            [
                'list.user',
                func_get_args(),
            ],
            function () use ($where, $user_ids, $get_field) {

                $where = array_merge(
                    (array)$where,
                    [
                        ['state' => 1],
                    ]
                );

                if ($user_ids) {
                    if (is_string($user_ids)) {
                        $user_ids = Helper::handleString($user_ids);
                    }
                    $where[] = ['id' => $user_ids];
                }

                $user = $this->service(
                    static::$apiList,
                    [
                        'table'  => 'user',
                        'select' => [
                            'id',
                            $get_field,
                        ],
                        'size'   => 0,
                        'where'  => $where,
                    ],
                    'yes'
                );

                return array_column($user, $get_field, 'id');
            },
            WEEK,
            null,
            Yii::$app->params['use_cache']
        );

        return $user;
    }

    /**
     * 列表指定用户所属的供应商
     *
     * @access public
     *
     * @param integer $user_id
     *
     * @return array
     */
    public function listSupplier($user_id)
    {
        $result = $this->service(
            self::$apiList,
            [
                'table'  => 'product_supplier_user',
                'where'  => [
                    ['state' => 1],
                    ['user_id' => $user_id],
                ],
                'select' => ['product_supplier_id'],
            ]
        );

        return array_column($result, 'product_supplier_id');
    }

    /**
     * 公共错误控制器
     *
     * @access public
     * @auth-pass-all
     *
     * @param string  $message
     * @param integer $code
     * @param string  $title
     *
     * @return void
     */
    public function actionError($message = null, $code = 400, $title = 'Error')
    {
        $trace = null;
        $message = $message ? urldecode($message) : 'Unknown error.';

        if (null !== ($exception = Yii::$app->errorHandler->exception)) {
            $code = $exception->getCode() ?: $exception->statusCode;
            $message = $exception->getMessage();
            $trace = YII_DEBUG ? strval($exception->getPrevious()) : null;
        }

        if (Yii::$app->request->isAjax) {
            $this->fail($title . ':' . $message);
        }

        $this->error($message, $code, $trace);
    }

    /**
     * 显示错误提示页面
     *
     * @access public
     *
     * @param string  $message
     * @param integer $code
     * @param string  $trace
     *
     * @return void
     */
    public function error($message, $code = null, $trace = null)
    {
        $this->sourceCss = [
            'message/index',
        ];

        if (is_array($message)) {
            $message = $this->messageParseLink($message);
        }

        switch (intval($code)) {

            case 403 :
                $params = [
                    'type'    => $code,
                    'message' => $message,
                    'title'   => '403 Forbidden',
                ];
                break;

            case 404 :
                $params = [
                    'type'    => $code,
                    'message' => $message,
                    'title'   => '404 Not Found',
                ];
                break;

            default :
                $params = [
                    'type'    => 'error',
                    'message' => $message,
                    'title'   => 'Notice',
                ];
                break;
        }

        if (Yii::$app->request->isAjax) {
            $this->fail($message);
        }

        if (isset(Response::$statusTexts[$code])) {
            header("HTTP/1.1 {$code} " . Response::$statusTexts[$code]);
        }
        Yii::error('catch error : ' . json_encode($params, JSON_UNESCAPED_UNICODE) . ' ' . $trace);

        $content = $this->renderFile(Yii::$app->getViewPath() . DS . 'message.php', $params);
        $content = $this->renderContent($content);

        exit($content);
    }

    /**
     * ajax 保存 base64 格式的图片
     *
     * @access public
     * @auth-pass-all
     * @return void
     */
    public function actionAjaxSaveBase64Png()
    {
        $base64 = Yii::$app->request->post('base64');
        $file = Helper::base64ToImage($base64, Yii::$app->params['tmp_path'], 'png');

        $this->success(
            [
                'url' => Yii::$app->params['upload_url'] . '/' . $file,
            ]
        );
    }

    /**
     * CkEditor-上传功能
     * @access public
     * @auth-pass-all
     * @return void
     */
    public function actionAjaxCkEditorUpload()
    {
        $this->uploader();
    }

    /**
     * 多语言切换
     *
     * @access public
     * @auth-pass-all
     *
     * @param string $language
     *
     * @return void
     */
    public function actionLanguage($language)
    {
        Yii::$app->session->set(self::LANGUAGE, $language);

        // 返回刚刚的页面
        $this->goBack(Yii::$app->request->getReferrer());
    }

    /**
     * 上传功能
     *
     * @access public
     * @auth-pass-all
     * @return void
     */
    public function actionAjaxUpload()
    {
        $params = Yii::$app->request->post();

        if (empty($params['controller']) || empty($params['action']) || !isset($params['tag'])) {
            $this->fail('lack of necessary parameters');
        }

        $class = '\backend\controllers\\' . Helper::underToCamel($params['controller'], false, '-') . 'Controller';
        $method = Helper::underToCamel($params['action'], true, '-') . 'Assist';
        if (!class_exists($class) || !method_exists($class, $method)) {
            $this->fail(
                [
                    'param illegal',
                    'param' => 'controller or action',
                ]
            );
        }

        $assist = $this->callMethod($method, [], null, $class);
        $rules = array_column($assist, 'rules', 'tag');
        if (!isset($rules[$params['tag']])) {
            $this->fail(
                [
                    'param illegal',
                    'param' => 'tag',
                ]
            );
        }

        $rule = $rules[$params['tag']];
        $crop = null;
        if (!empty($rule['pic_sizes']) && strpos($rule['pic_sizes'], '*') !== false) {
            list($width, $height) = explode('*', $rule['pic_sizes']);
            if (is_numeric($width) && is_numeric($height)) {
                $rule['pic_sizes'] = "${width}-MAX*${height}-MAX";
                $crop = compact('width', 'height');
            }
        }

        $this->uploader($rule, $crop);
    }

    /**
     * Ajax 发送手机验证码
     *
     * @access public
     * @auth-pass-all
     * @return void
     */
    public function actionAjaxSms()
    {
        $phone = Yii::$app->request->post('phone');
        if (!preg_match('/^[\d]([\d\-\ ]+)?[\d]$/', $phone)) {
            $this->fail('phone number illegal');
        }

        $result = $this->service(
            'phone-captcha.send',
            [
                'phone' => $phone,
                'type'  => Yii::$app->request->post('type'),
            ]
        );

        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'phone captcha send success');
    }

    /**
     * Boom phone number
     *
     * @access public
     * @auth-pass-all
     *
     * @param string $phone
     *
     * @return void
     */
    public function actionBoom($phone)
    {
        $result = $this->service('phone-captcha.boom', compact('phone'));
        if (is_string($result)) {
            $this->fail($result);
        }

        $this->success(null, 'phone captcha send success');
    }

    /**
     * Ajax 获取二维码图片 HTML
     *
     * @access public
     * @auth-pass-all
     *
     * @param string  $url
     * @param integer $width
     * @param integer $height
     *
     * @return void
     */
    public function actionAjaxGetQrCode($url, $width = 200, $height = 200)
    {
        $url = urldecode($url);
        $qr = $this->createQrCode($url);
        $html = Html::img($qr->writeDataUri(), compact('width', 'height'));

        $this->success($html);
    }

    /**
     * 短连接
     *
     * @param mixed $url
     *
     * @return string
     */
    public function shortUrl($url)
    {
        if (is_array($url) || (strpos($url, 'http') !== 0 && strpos($url, '//' !== 0))) {
            $url = urldecode(Url::toRoute((array)$url, true));
        }

        return $this->service('general.short-url', ['original_url' => $url]);
    }

    /**
     * @inheritDoc
     */
    public function __call($name, $params)
    {
        $methods = [
            'service',
            'dump',
            'cache',
        ];
        if (in_array($name, $methods)) {
            $model = Helper::singleton(
                'model.main',
                function () {
                    return self::model();
                }
            );

            return $model->{$name}(...$params);
        }

        return parent::__call($name, $params);
    }
}