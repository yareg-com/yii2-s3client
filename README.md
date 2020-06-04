# yii2-S3Client

[![Latest Stable Version](https://poser.pugx.org/yareg/yii2-s3client/v)](//packagist.org/packages/yareg/yii2-s3client)
[![Latest Unstable Version](https://poser.pugx.org/yareg/yii2-s3client/v/unstable)](//packagist.org/packages/yareg/yii2-s3client)
[![License](https://poser.pugx.org/yareg/yii2-s3client/license)](//packagist.org/packages/yareg/yii2-s3client)
[![Total Downloads](https://poser.pugx.org/yareg/yii2-s3client/downloads)](//packagist.org/packages/yareg/yii2-s3client)

Yii2 S3Client based on [klinson/aws-s3-minio](https://github.com/klinson/aws-s3-minio)

## Installation

Preferred way to install is through [Composer](https://getcomposer.org): 
```shell
php composer.phar require yareg/yii2-s3client:^1
```
Or, you may add

```php
"yareg/yii2-s3client": "^1"
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
$s3client->putFile(string $filePath, string $bucket, string $key, array  $meta = [], array  $tags = []);

/**
 * create and put a file into minio/s3 server with the specified content
 *
 * @param string $content
 * @param string $key
 * @param string $bucket
 * @param array $meta
 * @param array $tags
 * @return Result|bool
 * @throws AcmException
 */
$s3client->putContent(string $content, string $bucket, string $key, array  $meta = [], array  $tags = []);

/**
 * Get object and optionally save it as a file
 *
 * @param string|null $bucket
 * @param string $key
 * @param string|null $saveAs
 * @return mixed|null
 */
$s3client->getObject(string $bucket, string $key, string $saveAs = null)
```