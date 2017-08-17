<?php

namespace common\components;

use yii\base\Object;

/**
 * CSRF for submit form
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-09-19 18:56:56
 */
class Csrf extends Object
{

    /**
     * @var object instance of Session
     */
    public $session;

    /**
     * @var string key for save token
     */
    public $config = [
        'encrypt_salt' => 'csrf_k8k12JsqIk6Ooq',
        'form_key' => 'form_token',
        'input_tpl' => '<input type="hidden" name="%s" value="%s">'
    ];

    /**
     * Create token
     *
     * @access public
     *
     * @param boolean $inputModel
     * @param string  $prefix
     *
     * @return string
     * @throws \Exception
     */
    public function createToken($inputModel = true, $prefix = 'form')
    {

        if (empty($prefix)) {
            throw new \Exception('Please given param prefix.');
        }
        $token = md5(uniqid($this->config['encrypt_salt']));

        $key = $this->config['form_key'];
        $tokens = $this->session->get($key);
        if (!$tokens || !is_array($tokens)) {
            $tokens = [];
        }
        $tokens[$prefix] = $token;

        // Save
        $this->session->set($key, $tokens);
        $value = $prefix . '-' . $token;

        return $inputModel ? sprintf($this->config['input_tpl'], $key, $value) : $value;
    }

    /**
     * Check token
     *
     * @access public
     *
     * @param string $token
     *
     * @return mixed - boolean or string
     * @throws \Exception
     */
    public function check($token)
    {
        list($tag, $token) = explode('-', $token);

        $key = $this->config['form_key'];
        $_tokens = $this->session->get($key);

        if (!$_tokens || !isset($_tokens[$tag]) || $_tokens[$tag] != $token) {
            throw new \Exception('Please don\'t repeat submit form.');
        } else {
            if (isset($_POST[$key])) {
                unset($_POST[$key]);
            } else {
                if (isset($_GET[$key])) {
                    unset($_GET[$key]);
                }
            }
            unset($_tokens[$tag]);

            $this->session->delete($key);
            $this->session->set($key, $_tokens);

            return true;
        }
    }
}