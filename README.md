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
'components'=> [
    's3client' => [
        'class'=> 'yareg\S3Client',
        'key' => '<your key>',
        'secret' => '<yout secret>',
        'endpoint'=> '<your endpoint>',
        'region' => '<your region>', 
        'defaultBucket' => '<bucket>', //optional default bucket
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
 * put file to minio/s3 server
 * 
 * @param string $localObjectPath full path to file to put
 * @param string|null $storageSavePath full path to file in bucket (optional)
 * @param string|null $bucket the bucket name (optional)
 * @param array $meta (optional)
 * @return Result|bool
 */
$s3client->putObjectByPath(string $localObjectPath, string $storageSavePath = null, string $bucket = null, array $meta = []);

/**
 * create and put a file into minio/s3 server with the specified content
 * 
 * @param string $content
 * @param string $storageSavePath
 * @param string $bucket
 * @param array $meta
 * @return Result|bool
 */
$s3client->putObjectByContent(string $content, string $storageSavePath, string $bucket = null, array $meta = []);

/**
 * get file object from minio/s3 server 
 * 
 * @param string $storageSavePath
 * @param string|null $localSaveAsPath
 * @param string|null $bucket
 * @return bool|mixed
 */
$s3client->getObject(string $storageSavePath, string $localSaveAsPath = null, string $bucket = null)
```