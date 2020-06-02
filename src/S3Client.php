<?php

namespace yareg;

use Aws\Acm\Exception\AcmException;
use Aws\Exception\AwsException;
use Aws\Result;
use yii\base\Component;

class S3Client extends Component
{
    const BUCKETS = 'Buckets';

    /** @var \Aws\S3\S3Client */
    private $client;

    public $key;
    public $secret;
    public $region;
    public $endpoint;
    public $version = 'latest';
    public $http    = ['verify' => true];
    public $debug   = false;

    public function init()
    {
        parent::init();

        $this->client = new \Aws\S3\S3Client([
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ],
            'region'                  => $this->region,
            'endpoint'                => $this->endpoint,
            'version'                 => $this->version,
            'use_path_style_endpoint' => true,
            'http'                    => $this->http,
            'debug'                   => $this->debug
        ]);
    }

    /**
     * @return array|mixed
     */
    public function listBuckets() {
        $data = $this->client->listBuckets();
        if (is_array($data) && $data[self::BUCKETS]) {
            return $data[self::BUCKETS];
        }
        return [];
    }

    /**
     * @param String|null $bucket
     * @return bool
     */
    public function createBucket(string $bucket)
    {
        if (is_null($bucket)) {
            return false;
        }

        try {
            $this->client->createBucket(['Bucket' => $bucket]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * @param string|null $bucket
     * @return bool
     */
    public function deleteBucket(string $bucket)
    {
        if (is_null($bucket)) {
            return false;
        }

        try {
            $this->client->deleteBucket(['Bucket' => $bucket]);
            return true;
        } catch (AwsException $awsException) {
            return false;
        }
    }

    /**
     * Put file to minio/s3 server
     *
     * @param string $filePath full path to file to put
     * @param string|null $key full path to file in bucket (optional)
     * @param string|null $bucket the bucket name (optional)
     * @param array $meta
     * @param array $tags
     * @return Result|bool
     * @throws AcmException
     */
    public function putFile(
        string $filePath,
        string $bucket,
        string $key,
        array  $meta = [],
        array  $tags = [])
    {
        if (is_null($filePath) || is_null($bucket) || is_null($key)) {
            return false;
        }

        return $this->client->putObject([
            'Bucket'     => $bucket,
            'Key'        => $key,
            'SourceFile' => $filePath,
            'Metadata'   => $this->buildMeta($meta),
            'Tagging'    => $this->buildTags($tags)
        ]);
    }

    /**
     * Create and put a file into minio/s3 server with the specified content
     *
     * @param string $content
     * @param string $key
     * @param string $bucket
     * @param array $meta
     * @param array $tags
     * @return Result|bool
     * @throws AcmException
     */
    public function putContent(
        string $content,
        string $bucket,
        string $key,
        array  $meta = [],
        array  $tags = [])
    {
        if (is_null($content) || is_null($bucket) || is_null($key)) {
            return false;
        }

        return $this->client->putObject([
            'Bucket'   => $bucket,
            'Key'      => $key,
            'Body'     => $content,
            'Metadata' => $this->buildMeta($meta),
            'Tagging'  => $this->buildTags($tags)
        ]);
    }

    /**
     * Get object and optionally save it as a file
     *
     * @param string|null $bucket
     * @param string $key
     * @param string|null $saveAs
     * @return mixed|null
     */
    public function getObject(string $bucket, string $key, string $saveAs = null)
    {
        try {
            $param = [
                'Bucket' => $bucket,
                'Key'    => $key,
            ];

            if (!is_null($saveAs)) {
                $param['SaveAs'] = $saveAs;
            }

            return $this->client->getObject($param)['Body'];
        } catch (AwsException $awsException) {
            return null;
        }
    }

    /**
     * @param array $meta
     * @return array
     */
    private function buildMeta(array $meta): array
    {
        if (!empty($meta)) {
            foreach ($meta as $k => $v) {
                unset($meta[$k]);
                if (is_null($v)) continue;
                $v = (string)$v;
                $meta[$k] = $v;
            }
        }
        return $meta;
    }

    /**
     * @param array $tags
     * @return string|null
     */
    private function buildTags(array $tags)
    {
        if (empty($tags)) return null;
        return http_build_query($tags);
    }
}