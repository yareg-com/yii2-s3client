# yii2-S3Client

[![Latest Stable Version](https://poser.pugx.org/yareg/yii2-s3client/v)](//packagist.org/packages/yareg/yii2-s3client)
[![Latest Unstable Version](https://poser.pugx.org/yareg/yii2-s3client/v/unstable)](//packagist.org/packages/yareg/yii2-s3client)
[![License](https://poser.pugx.org/yareg/yii2-s3client/license)](//packagist.org/packages/yareg/yii2-s3client)
[![Total Downloads](https://poser.pugx.org/yareg/yii2-s3client/downloads)](//packagist.org/packages/yareg/yii2-s3client)

Yii2 S3Client based on [klinson/aws-s3-minio](https://github.com/klinson/aws-s3-minio)

## Installation

Preferred way to install is through [Composer](https://getcomposer.org): 
```shell
php composer.phar require yareg/yii2-s3client:^2
```
Or, you may add

```php
"yareg/yii2-s3client": "^2"
```

to the require section of your `composer.json` file and execute `php composer.phar update`.

## Configuration

in ``web.php``

```php
'components' => [
    's3client' => [
        'class'=> 'yareg\S3Client',
        'key' => '<your key>',
        'secret' => '<your secret>',
        'endpoint'=> '<your endpoint>',
        'region' => '<your region>',
        'debug' => false, // optional
        'http' => [ //optional
            'verify' => false //use false to self-signed certs
        ],
    ],
],
```

## Usage

```
/** @var S3Client $s3client */
$s3client = Yii::$app->s3client;
```
##### Bucket operations
````
/**
 * @return array|mixed
 */
$s3client->listBuckets();

/**
 * @param string $bucket
 * @return bool
 */
$s3client->createBucket('test');

/**
 * @param string $bucket
 * @return bool
 */
$s3client->deleteBucket('test');

/**
 * @param string $bucket
 * @return bool
 */
$s3client->publishBucket('test'); // WARNING! Your files will be publicly accessible!

/**
 * @param string $bucket
 * @return bool
 */
$s3client->deleteBucketPolicy('test'); // May be used to unpublish the bucket
````

##### Object operations

```
/**
 * @param string $filePath
 * @param string $bucket
 * @param string $key
 * @param array $meta
 * @param array $tags
 * @param array $args
 * @return bool
 */
$s3client->putFile(string $filePath, string $bucket, string $key, array $meta = [], array $tags = [], array $args = []);

/**
 * @param string $content
 * @param string $bucket
 * @param string $key
 * @param array $meta
 * @param array $tags
 * @param array $args
 * @return bool
 */
$s3client->putContent(string $content, string $bucket, string $key, array $meta = [], array $tags = [], array $args = []);

/**
 * @param string $bucket
 * @param string $key
 * @param string|null $saveAs
 * @return mixed|null
 */
$s3client->getObject(string $bucket, string $key, string $saveAs = null);

/**
 * @param string $bucket
 * @param string $prefix
 */
$s3client->listObjects(string $bucket, string $prefix = null);

/**
 * @param string $bucket
 * @param string $key
 * @return bool
 */
$s3client->objectExists(string $bucket, string $key);

/**
 * @param string $bucket
 * @param array $keys
 * @return bool
 */
$s3client->deleteObjects(string $bucket, array $keys = []);
```