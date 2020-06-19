<?php

namespace yareg;

use Aws\Exception\AwsException;
use yii\base\Component;

class S3Client extends Component
{
    const BUCKETS     = 'Buckets';
    const BUCKET      = 'Bucket';
    const POLICY      = 'Policy';
    const KEY         = 'Key';
    const BODY        = 'Body';
    const SOURCE_FILE = 'SourceFile';
    const METADATA    = 'Metadata';
    const TAGGING     = 'Tagging';
    const DELETE      = 'Delete';
    const SAVE_AS     = 'SaveAs';
    const OBJECTS     = 'Objects';
    const PREFIX      = 'Prefix';
    const CONTENTS    = 'Contents';

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
     * @return \Aws\S3\S3Client
     */
    public function getClient(): \Aws\S3\S3Client
    {
        return $this->client;
    }

    /* -----------------------------------------------------------------------------------------------------------------
     *
     *  BUCKET OPERATIONS
     *
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * @return array|mixed
     */
    public function listBuckets() {
        try {
            $data = $this->client->listBuckets();
            if ($data[self::BUCKETS]) {
                return $data[self::BUCKETS];
            }
            return [];
        } catch (AwsException $e) {
            return [];
        }
    }

    /**
     * @param string $bucket
     * @return bool
     */
    public function createBucket(string $bucket)
    {
        try {
            $this->client->createBucket([self::BUCKET => $bucket]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * @param string $bucket
     * @return bool
     */
    public function deleteBucket(string $bucket)
    {
        try {
            $this->client->deleteBucket([self::BUCKET => $bucket]);
            return true;
        } catch (AwsException $awsException) {
            return false;
        }
    }

    /**
     * @param string $bucket
     * @return bool
     */
    public function publishBucket(string $bucket)
    {
        try {
            $this->client->putBucketPolicy([
                self::BUCKET => $bucket,
                self::POLICY => self::createPublicPolicy($bucket)
            ]);
            return true;
        } catch (AwsException $awsException) {
            return false;
        }
    }

    /**
     * @param string $bucket
     * @return bool
     */
    public function deleteBucketPolicy(string $bucket)
    {
        try {
            $this->client->deleteBucketPolicy([
                self::BUCKET => $bucket
            ]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /* -----------------------------------------------------------------------------------------------------------------
     *
     *  OBJECT OPERATIONS
     *
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * @param string $bucket
     * @param string $prefix
     */
    public function listObjects(string $bucket, string $prefix = null)
    {
        $objects = [];
        try {
            $result = $this->client->listObjectsV2([
                self::BUCKET => $bucket,
                self::PREFIX => $prefix
            ]);
            return $result[self::CONTENTS];
        } catch (AwsException $e) {
            return $objects;
        }
    }

    /**
     * @param string $bucket
     * @param string $key
     * @param string|null $saveAs
     * @return mixed|null
     */
    public function getObject(string $bucket, string $key, string $saveAs = null)
    {
        try {
            $param = [
                self::BUCKET => $bucket,
                self::KEY    => $key,
            ];

            if (!is_null($saveAs)) {
                $param[self::SAVE_AS] = $saveAs;
            }

            return $this->client->getObject($param)[self::BODY];
        } catch (AwsException $e) {
            return null;
        }
    }

    /**
     * @param string $bucket
     * @param string $key
     * @return bool
     */
    public function objectExists(string $bucket, string $key)
    {
        return $this->client->doesObjectExist($bucket, $key);
    }

    /**
     * @param string $filePath
     * @param string $bucket
     * @param string $key
     * @param array $meta
     * @param array $tags
     * @param array $args
     * @return bool
     */
    public function putFile(
        string $filePath,
        string $bucket,
        string $key,
        array  $meta = [],
        array  $tags = [],
        array  $args = [])
    {
        try {

            $base = [
                self::BUCKET      => $bucket,
                self::KEY         => $key,
                self::SOURCE_FILE => $filePath,
                self::METADATA    => $this->buildMeta($meta),
                self::TAGGING     => $this->buildTags($tags)
            ];

            $this->client->putObject(array_merge($base, $args));
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * @param string $content
     * @param string $bucket
     * @param string $key
     * @param array $meta
     * @param array $tags
     * @param array $args
     * @return bool
     */
    public function putContent(
        string $content,
        string $bucket,
        string $key,
        array  $meta = [],
        array  $tags = [],
        array  $args = [])
    {
        try {

            $base = [
                self::BUCKET   => $bucket,
                self::KEY      => $key,
                self::BODY     => $content,
                self::METADATA => $this->buildMeta($meta),
                self::TAGGING  => $this->buildTags($tags)
            ];

            $this->client->putObject(array_merge($base, $args));
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * @param string $bucket
     * @param array $keys
     * @return bool
     */
    public function deleteObjects(string $bucket, array $keys = [])
    {
        if (empty($keys)) {
            return false;
        }

        $objects = [];
        foreach($keys as $key) {
            $objects[] = [self::KEY => $key];
        }

        try {
            $this->client->deleteObjects([
                self::BUCKET => $bucket,
                self::DELETE => [self::OBJECTS => $objects]
            ]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /* -----------------------------------------------------------------------------------------------------------------
     *
     *  UTILITY METHODS
     *
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * @param string $bucket
     * @return false|string
     */
    public static function createPublicPolicy(string $bucket)
    {
        $policy = [
            'Version' => '2012-10-17',
            'Statement' => [
                [
                    'Sid'       => 'Public',
                    'Effect'    => 'Allow',
                    'Principal' => ['AWS' => ['*']],
                    'Action'    => ['s3:GetObject'],
                    'Resource'  => ['arn:aws:s3:::'.$bucket.'/*']
                ]
            ]
        ];
        return json_encode($policy,JSON_UNESCAPED_SLASHES);
    }

    /* -----------------------------------------------------------------------------------------------------------------
     *
     *  PRIVATE METHODS
     *
     * -----------------------------------------------------------------------------------------------------------------
     */

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