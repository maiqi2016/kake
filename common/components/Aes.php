<?php

namespace common\components;

use yii\base\Object;

/**
 * Aes components
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2017-08-15 16:05:27
 */
class Aes extends Object
{
    /**
     * @var string $iv
     */
    public $iv;

    /**
     * @var string $key
     */
    public $key;

    /**
     * @var string $model
     */
    public $mode;

    /**
     * @var integer $bit
     */
    public $bit = 128;

    /**
     * @var integer $cipher
     */
    private $cipher;

    /**
     * Aes constructor.
     *
     * @param string  $iv
     * @param string  $key
     * @param integer $mode
     * @param integer $bit
     */
    public function __construct($iv, $key, $mode = null, $bit = null)
    {
        parent::__construct();

        $items = [
            'iv',
            'key',
            'mode',
            'bit'
        ];
        foreach ($items as $item) {
            if (isset(${$item})) {
                $this->{$item} = ${$item};
            }
        }

        switch ($this->bit) {
            case 192:
                $this->cipher = MCRYPT_RIJNDAEL_192;
                break;
            case 256:
                $this->cipher = MCRYPT_RIJNDAEL_256;
                break;
            default:
                $this->cipher = MCRYPT_RIJNDAEL_128;
        }

        switch (strtoupper($this->mode)) {
            case 'ECB':
                $this->mode = MCRYPT_MODE_ECB;
                break;
            case 'CFB':
                $this->mode = MCRYPT_MODE_CFB;
                break;
            case 'OFB':
                $this->mode = MCRYPT_MODE_OFB;
                break;
            case 'NOFB':
                $this->mode = MCRYPT_MODE_NOFB;
                break;
            case 'CBC':
            default:
                $this->mode = MCRYPT_MODE_CBC;
        }
    }

    /**
     * Encrypt
     *
     * @param string $data
     *
     * @return string
     */
    public function encrypt($data)
    {
        return base64_encode(mcrypt_encrypt($this->cipher, $this->key, $data, $this->mode, $this->iv));
    }

    /**
     * Decrypt
     *
     * @param string $data
     *
     * @return string
     */
    public function decrypt($data)
    {
        return mcrypt_decrypt($this->cipher, $this->key, base64_decode($data), $this->mode, $this->iv);
    }
}