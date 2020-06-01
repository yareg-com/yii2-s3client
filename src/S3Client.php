<?php

namespace yareg;

use Aws\Acm\Exception\AcmException;
use Aws\Exception\AwsException;
use Aws\Result;
use yii\base\Component;

class S3Client extends Component
{
    /** @var \Aws\S3\S3Client */
    private $client;

    public $key;
    public $secret;
    public $region;
    public $version = 'latest';
    public $endpoint;
    public $profile = 'default';
    public $defaultBucket;
    public $debug = false;
    public $http = ['verify' => true];

    public function init()
    {
        parent::init();

        $this->client = new \Aws\S3\S3Client([
            'credentials' => [
                'key'    => $this->key ?? '',
                'secret' => $this->secret ?? '',
            ],
            'region'                  => $this->region ?? '',
            'version'                 => $this->version ?? 'latest',
            'endpoint'                => $this->endpoint ?? '',
            'use_path_style_endpoint' => true,
            'debug'                   => $this->debug,
            'http'                    => $this->http
        ]);
    }

    /**
     * @param String|null $bucket
     * @return bool
     */
    public function createBucket(string $bucket = null)
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
    public function deleteBucket(string $bucket = null)
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
     * put file to minio/s3 server
     *
     * @param string $localObjectPath full path to file to put
     * @param string|null $storageSavePath full path to file in bucket (optional)
     * @param string|null $bucket the bucket name (optional)
     * @param array $meta
     * @param array $tags
     * @return Result|bool
     * @throws AcmException
     */
    public function putObjectByPath(string $localObjectPath, string $storageSavePath = null, string $bucket = null, array $meta = [], array $tags = [])
    {
        $bucket = $bucket ?? $this->defaultBucket;

        if (empty($bucket)) {
            return false;
        }

        $this->createBucket($bucket);

        if ($storageSavePath === null) {
            $storageSavePath = $localObjectPath;
        }

        $meta = $this->cleanMeta($meta);
        $tags = $this->normalizeTags($tags);

        $storageSavePath = $this->formatStorageSavePath($storageSavePath);

        return $this->client->putObject([
            'Bucket' => $bucket,
            'Key' => $storageSavePath,
            'SourceFile' => $localObjectPath,
            'Metadata' => $meta,
            'Tagging' => $tags,
        ]);
    }

    /**
     * create and put a file into minio/s3 server with the specified content
     *
     * @param string $content
     * @param string $storageSavePath
     * @param string $bucket
     * @param array $meta
     * @param array $tags
     * @return Result|bool
     * @throws AcmException
     */
    public function putObjectByContent(string $content, string $storageSavePath, string $bucket = null, array $meta = [], array $tags = [])
    {
        $bucket = $bucket ?? $this->defaultBucket;

        if (empty($bucket)) {
            return false;
        }

        $this->createBucket($bucket);

        $meta = $this->cleanMeta($meta);
        $tags = $this->normalizeTags($tags);

        $storageSavePath = $this->formatStorageSavePath($storageSavePath);

        return $this->client->putObject([
            'Bucket' => $bucket,
            'Key' => $storageSavePath,
            'Body' => $content,
            'Metadata' => $meta,
            'Tagging' => $tags,
        ]);
    }


    /**
     * @param string $storageSavePath
     * @param string|null $localSaveAsPath
     * @param string|null $bucket
     * @return mixed|null
     */
    public function getObject(string $storageSavePath, string $localSaveAsPath = null, string $bucket = null)
    {
        $bucket = $bucket ?? $this->defaultBucket;

        if (empty($bucket)) {
            return null;
        }

        try {
            $param = [
                'Bucket' => $bucket,
                'Key' => $storageSavePath,
            ];
            if (!is_null($localSaveAsPath)) {
                $param[] = [
                    'SaveAs' => $localSaveAsPath
                ];
            }

            $result = $this->client->getObject($param);
            return $result['Body'];
        } catch (AwsException $awsException) {
            return null;
        }
    }

    /**
     * @param string $storageSavePath
     * @return string
     * @author klinson <klinson@163.com>
     */
    private function formatStorageSavePath(string $storageSavePath)
    {
        return trim($storageSavePath, '/');
    }

    /**
     * @param array $meta
     * @return array
     */
    private function cleanMeta(array $meta): array
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
    private function normalizeTags(array $tags)
    {
        if (empty($tags)) return null;
        return http_build_query($tags);
    }
}