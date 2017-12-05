<?php

namespace common\components;

use common\components\Helper;
use yii\base\Object;
use Yii;
use yii\helpers\Url;

/**
 * Sso Client components
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2017-08-14 09:41:32
 */
class SsoClient extends Object
{
    /**
     * @var string Code key
     */
    public static $responseType = 'ticket'; // oAuth2.0 use `code`

    /**
     * @var string SSO host
     */
    public static $ssoHost = 'http://passport.kakehotels.com/';

    /**
     * Get url by api and params
     *
     * @param string $api
     * @param array  $params
     *
     * @return string
     */
    public static function getUrl($api, $params = [])
    {
        $api = '/' . str_replace('.', '/', $api);
        $url = self::$ssoHost . Url::toRoute(array_merge([$api], $params));

        return rtrim($url, '&');
    }

    /**
     * Get client id
     *
     * @return string
     */
    public static function getClientId()
    {
        $host = explode(':', $_SERVER['HTTP_HOST']);
        $clientId = md5(strrev($host[0]));

        return $clientId;
    }

    /**
     * Handle url
     *
     * @param string $url
     *
     * @return mixed
     */
    public static function handleUrl($url)
    {
        $url = str_replace('index.php', '', $url);
        $url = str_replace('/?', '?', $url);

        return $url;
    }

    /**
     * Redirect for get code
     *
     * @param string $redirect_uri
     * @param string $scope
     * @param string $state
     *
     * @return object
     */
    public static function code($redirect_uri, $scope = 'OAUTH', $state = 'STATE')
    {
        $url = self::getUrl('auth.code', [
            'response_type' => self::$responseType,
            'client_id' => self::getClientId(),
            'redirect_uri' => self::handleUrl($redirect_uri),
            'scope' => $scope,
            'state' => $state
        ]);

        header('Location: ' . $url);
        exit();
    }

    /**
     * 处理响应内容
     *
     * @param string $response
     *
     * @return mixed
     * @throws \Exception
     */
    private static function handleResponse($response)
    {
        $result = json_decode($response, true);

        if (is_null($result)) {
            exit($response);
        }

        if (isset($result['info'])) {
            return $result['info'];
            // throw new \Exception($result['info']);
        }

        return $result;
    }

    /**
     * 获取 token
     *
     * @param string $redirect_uri
     * @param string $code
     *
     * @return mixed
     */
    public static function token($redirect_uri, $code = null)
    {
        $redirect_uri = Helper::unsetParamsForUrl([
            self::$responseType,
            'state'
        ], $redirect_uri);

        $code = $code ?: Yii::$app->request->get(self::$responseType);

        $url = self::getUrl('auth.token');
        $response = Helper::cURL($url, 'post', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => self::handleUrl($redirect_uri),
            'client_id' => self::getClientId()
        ], 'http_build_query');

        return self::handleResponse($response);
    }

    /**
     * 使用 token 调用接口
     *
     * @param string $api
     * @param string $token
     *
     * @return mixed
     */
    public static function api($api, $token)
    {
        $url = self::getUrl($api);
        $response = Helper::cURL($url, 'post', [
            'token' => $token,
            'client_id' => self::getClientId()
        ], 'http_build_query');

        return self::handleResponse($response);
    }

    /**
     * 登出
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     *
     * @return boolean
     */
    public static function logout($name, $path = null, $domain = null)
    {
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

        session_destroy();
        foreach ((array) $name as $item) {
            setcookie($item, null, TIME, $path, $domain);
        }

        return true;
    }

    // ---

    /**
     * 授权
     *
     * @param string $url
     * @param string $tokenKey
     *
     * @return mixed
     */
    public static function auth($url = null, $tokenKey = 'sso_token')
    {
        $url = $url ?: Helper::currentUrl();

        $token = Yii::$app->session->get($tokenKey);
        if (!$token) {
            if (!Yii::$app->request->get(self::$responseType)) {
                self::code($url);
            }

            $token = self::token($url);
            if (is_string($token)) {
                return $token;
            }

            $token = $token['access_token'];
            Yii::$app->session->set($tokenKey, $token);
        }

        try {
            $result = self::api('user.info', $token);
        } catch (\Exception $e) {

            $msg = $e->getMessage();
            if (strpos($msg, 'already expire') !== false) {
                Yii::$app->session->remove($tokenKey);

                $url = Helper::unsetParamsForUrl([
                    self::$responseType,
                    'state'
                ], $url);

                header('Location: ' . $url);
                exit();
            }

            return $msg;
        }

        return $result;
    }
}