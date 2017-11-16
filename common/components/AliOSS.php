<?php

namespace common\components;

use yii\base\Object;
use Yii;
use OSS\OssClient;
use OSS\Core\OssException;

/**
 * AliOSS components
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2017-11-15 13:46:49
 */
class AliOSS extends Object
{
    /**
     * @var string Access ID
     */
    private $accessKeyId;

    /**
     * @var string Access Secret
     */
    private $accessKeySecret;

    /**
     * @var string Endpoint
     */
    private $endpoint;

    /**
     * @var string Host
     */
    public $host;

    /**
     * @var string Bucket
     */
    public $bucket;

    /**
     * @var object Instance
     */
    public $client;

    /**
     * __constructor
     *
     * @access public
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->accessKeyId = $config['id'];
        $this->accessKeySecret = $config['secret'];
        $this->endpoint = $config['endpoint'];

        $this->bucket = $config['default_bucket'];
        $this->host = Helper::issetDefault(Yii::$app->params, 'upload_url', $config, true);

        $this->client = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);

        parent::__construct();
    }

    /**
     * Upload
     *
     * @access public
     *
     * @param string $file
     * @param string $rename
     *
     * @return mixed
     */
    public function upload($file, $rename = null)
    {
        $rename = $rename ?: basename($file);
        try {
            $this->client->uploadFile($this->bucket, $rename, $file);
        } catch (OssException $e) {
            return $e->getMessage();
        }

        return true;
    }
}