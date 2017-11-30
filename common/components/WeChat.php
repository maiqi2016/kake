<?php

namespace common\components;

use yii;
use yii\base\Object;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

/**
 * WeChat SDK
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-12-21 10:09:41
 */
class WeChat extends Object
{
    /**
     * @const space for zh-cn model
     */
    const SPACE = '　';

    /**
     * @var object SDK instance
     */
    public $app;

    /**
     * @var array For listen events
     */
    public $listenFn;

    /**
     * __constructor
     *
     * @access public
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $request = Yii::$app->request;

        if (method_exists($request, 'get') && $valid = $request->get('echostr')) {
            $signatureArray = [
                $config['token'],
                $request->get('timestamp'),
                $request->get('nonce')
            ];
            sort($signatureArray);

            if (sha1(implode($signatureArray)) == $request->get('signature')) {
                header('content-type:text');
                exit($valid);
            }
        }

        // Payment
        if (isset($config['payment']) && isset($config['oauth'])) {
            $config['payment']['cert_path'] = Yii::getAlias($config['payment']['cert_path']);
            $config['payment']['key_path'] = Yii::getAlias($config['payment']['key_path']);
            $config['oauth']['callback'] = Helper::issetDefault(Yii::$app->params, 'wechat_callback', $config, true);
        }
        $this->app = new Application($config);

        parent::__construct();
    }

    /**
     * Listen message
     *
     * @param array $fnArray
     *
     * @return object
     */
    public function listen($fnArray)
    {
        $this->listenFn = $fnArray;
        $this->server->setMessageHandler(function ($message) {

            $reply = null;
            $type = strtolower($message->MsgType);

            if (!empty($message->EventKey)) {
                $message->EventKey = str_replace('qrscene_', null, $message->EventKey);
            }

            if (empty($this->listenFn[$type])) {
                $function = 'reply' . ucfirst($type);
                $this->listenFn[$type] = [
                    $this,
                    $function
                ];
            }

            if ($this->listenFn[$type]) {
                $reply = call_user_func($this->listenFn[$type], $message);
            }

            return $reply;
        });

        return $this->server->serve()->send();
    }

    /**
     * Listen event
     *
     * @param object $message
     *
     * @return string
     */
    public function replyEvent($message)
    {
        $reply = null;
        $event = strtolower($message->Event);
        $fn = 'event_' . $event;

        $text = function ($defaultReply = null) use ($fn, $message) {
            if (!empty($this->listenFn[$fn])) {
                $reply = call_user_func($this->listenFn[$fn], $message);
            } else {
                $reply = $defaultReply;
            }

            return $reply;
        };

        switch ($event) {
            // 关注
            case 'subscribe' :
                $reply = $text('welcome subscribe us.');
                break;

            // 取消关注
            case 'unsubscribe' :
                $reply = $text();
                break;

            // 扫码
            case 'scan' :
                $reply = $text();
                break;

            // 点击菜单拉取消息
            case 'click' :
                $reply = $text();
                break;

            // 点击菜单跳转页面
            case 'view' :
                $reply = $text();
                break;

            // 上报地理位置
            case 'location' :
                $reply = $text();
                break;

            default:
                break;
        }

        return $reply;
    }

    /**
     * Listen text
     *
     * @param object $message
     *
     * @return string
     */
    public function replyText($message)
    {
        // return 'you say: ' . $message->Content;
        return null;
    }

    /**
     * Get group id by name when not exists create it
     *
     * @access public
     *
     * @param string $name
     *
     * @return integer
     */
    public function group($name)
    {
        $groups = $this->user_group->lists()->groups;
        $groups = array_combine(array_column($groups, 'name'), $groups);

        if (isset($groups[$name])) {
            $id = $groups[$name]['id'];
        } else {
            $id = $this->user_group->create($name)['group']['id'];
        }

        return $id;
    }

    /**
     * Set and get config
     *
     * @access public
     *
     * @param null $name
     * @param null $value
     *
     * @return object
     */
    public function config($name = null, $value = null)
    {
        $config = $this->app['config'];
        if (!empty($name) && !empty($value)) {
            $config->set($name, $value);
        }

        return $config;
    }

    /**
     * Auth
     *
     * @access public
     * @return void
     */
    public function auth()
    {
        $this->oauth->redirect()->send();
    }

    /**
     * Get user info
     *
     * @access public
     * @return array
     */
    public function user()
    {
        return $this->oauth->user()->getOriginal();
    }

    /**
     * Create order
     *
     * @access public
     *
     * @param array $params
     *
     * @return mixed
     */
    public function order($params)
    {
        $timeout = Helper::issetDefault(Yii::$app->params, 'order_pay_timeout', 30);
        $attributes = array_merge([
            'trade_type' => 'JSAPI',
            'time_start' => date('YmdHis', TIME),
            'time_expire' => date('YmdHis', TIME + $timeout * MINUTE)
        ], $params);
        $order = new Order($attributes);

        $result = $this->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            return $result->prepay_id;
        }

        return $result;
    }

    /**
     * Get menu
     *
     * @access public
     * @return array
     */
    public function getMenu()
    {
        $menu = Yii::$app->wx->menu->current();
        $menu = empty($menu->selfmenu_info['button']) ? [] : $menu->selfmenu_info['button'];
        foreach ($menu as &$item) {
            if (!isset($item['sub_button'])) {
                continue;
            }
            $item['sub_button'] = $item['sub_button']['list'];
        }

        return $menu;
    }

    /**
     * Get message template
     *
     * @access public
     * @return array
     */
    public function getMsgTpl()
    {
        static $tpl, $field, $space;

        if (!$tpl) {

            // 获取模板
            $tpl = Yii::$app->wx->notice->getPrivateTemplates()->template_list;
            array_shift($tpl);

            // 解析字段
            $space = $field = $this->parseTplMsgField($tpl);

            // 计算字段要追加的空格数
            foreach ($space as $key => &$value) {
                $value = array_map('mb_strlen', $value);
                $max = max($value);

                foreach ($value as &$val) {
                    $val = ($val < $max) ? intval($max - $val) : 0;
                }
            }
        }

        return [
            $tpl,
            $field,
            $space
        ];
    }

    /**
     * Parse message to fields
     *
     * @access public
     *
     * @param mixed $tpl
     *
     * @return array
     */
    public function parseTplMsgField($tpl = null)
    {
        is_null($tpl) && list($tpl) = $this->getMsgTpl();

        $json = array_column($tpl, 'content', 'template_id');
        $parse = function ($item, $id) {

            $split = '：';

            if (empty($item)) {
                return null;
            }

            if (strpos($item, $split) === false) {
                $info = '无标字段';
                $field = $item;
            } else {
                list($info, $field) = explode($split, $item);
            }

            $field = preg_replace('/.*\{\{([\w\d]+).DATA\}\}.*/i', "$1", $field);

            return [$field => $info];
        };

        $_json = [];
        foreach ($json as $id => $content) {
            $field = explode(PHP_EOL, $content);
            $field = array_slice($field, 1, -1);
            $field = array_map($parse, $field, array_fill(0, count($field), $id));
            $field = array_filter($field);

            $_json[$id] = array_merge(...$field);
        }

        return $_json;
    }

    /**
     * Send templates message
     *
     * @access public
     *
     * @param array $params
     * @param array $keywords
     *
     * @return void
     */
    public function sendTplMsg($params, $keywords = [])
    {
        $message = [
            'touser' => $params['to'],
            'template_id' => $params['tpl'],
            'url' => empty($params['url']) ? null : $params['url'],
            'data' => [
                'first' => [
                    empty($params['header']) ? null : ($params['header'] . PHP_EOL),
                    '#2d3e50'
                ],
                'remark' => [
                    empty($params['footer']) ? null : (PHP_EOL . $params['footer']),
                    '#fda443'
                ]
            ],
        ];

        list($tpl, $field, $space) = $this->getMsgTpl();

        foreach ($keywords as $key => $value) {
            if (is_numeric($key)) {
                $key = 'keyword' . ($key + 1);
            }
            $message['data'][$key] = [
                str_repeat(self::SPACE, intval($space[$params['tpl']][$key])) . $value,
                '#999'
            ];
        }

        $this->notice->send($message);
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
        return $this->app->{$name};
    }
}
