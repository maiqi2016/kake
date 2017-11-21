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
