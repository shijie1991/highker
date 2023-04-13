SinaCloudStorage-SDK-PHP
========================

PHP SDK for 新浪云存储


### Requirements

* PHP >= 5.2.0
* [PHP cURL]

### Installation

* 可以使用`composer.phar`进行安装, composer require cloudmario/scs:dev-master 详细信息：https://packagist.org/packages/cloudmario/scs
* 也可以直接引用源码

### Usage

OO method (e,g; $scs->getObject(...)):

```php
$scs = new SCS($scsAccessKey, $scsSecretKey);
```

Statically (e,g; SCS::getObject(...)):

```php
SCS::setAuth($scsAccessKey, $scsSecretKey);
```

Use exceptions

```php
SCS::setExceptions(true);

try
{
	$response = SCS::getObjectInfo($bucket, $uri);
	print_r($response);
}
catch(SCSException $e)
{
	echo $e->getMessage();
}
```


### Object Operations

#### Uploading objects

Put an object from a file:

```php
SCS::putObject(SCS::inputFile($file, false), $bucketName, $uploadName, SCS::ACL_PUBLIC_READ);
```

Put an object from a string and set its Content-Type:

```php
SCS::putObject($string, $bucketName, $uploadName, SCS::ACL_PUBLIC_READ, array(), array('Content-Type' => 'text/plain'));
```

Put an object from a resource (buffer/file size is required - note: the resource will be fclose()'d automatically):

```php
SCS::putObject(SCS::inputResource(fopen($file, 'rb'), filesize($file)), $bucketName, $uploadName, SCS::ACL_PUBLIC_READ);
```

Also use:

```php
SCS::putObjectFile($file, $bucket, $uri);
SCS::putObjectString($string, $bucket, $uri);
```

#### Retrieving objects

Get an object:

```php
SCS::getObject($bucketName, $uploadName);
```

Get an object info (meta):

```php
SCS::getObjectInfo($bucket, $uri, $returnInfo = true);
```

You can also:

```php
SCS::getMeta($bucket, $uri);
```

Save an object to file:

```php
SCS::getObject($bucketName, $uploadName, $saveName);
```

Save an object to a resource of any type:

```php
SCS::getObject($bucketName, $uploadName, fopen('savefile.txt', 'wb'));
```

#### Copying and deleting objects

Copy an object:

```php
SCS::copyObject($srcBucket, $srcName, $bucketName, $saveName, $metaHeaders = array(), $requestHeaders = array());
```

Delete an object:

```php
SCS::deleteObject($bucketName, $uploadName);
```

### Bucket Operations

Get a list of buckets:

```php
SCS::listBuckets();  // Simple bucket list
SCS::listBuckets(true);  // Detailed bucket list
```

Create a bucket:

```php
SCS::putBucket($bucketName);
```

Get the contents of a bucket (list objects):

```php
SCS::getBucket($bucketName);
```

Delete an empty bucket:

```php
SCS::deleteBucket($bucketName);
```

Get bucket meta:

```php
SCS::getMeta($bucketName);
```

### ACL Operations

Get ACL

```php
SCS::getAccessControlPolicy($bucket); //for bucket
SCS::getAccessControlPolicy($bucket, $uri); //for object
```

Set ACL

```php
$acp = array(
	
	'GRPS000000ANONYMOUSE' => array('read'),
	'GRPS0000000CANONICAL' => array('read', 'write'),
	'SINA0000001001HBK300' => array('read', 'write', 'read_acp', 'write_acp')
);

SCS::setAccessControlPolicy($bucket, $uri, $acp); //for object
SCS::setAccessControlPolicy($bucket, '', $acp); //for bucket
```

### Examples


#### 基本示例:

* 文件: examples/example.php

#### 表单上传

* 文件: examples/example-form.php

#### 实现一个Wrapper

* 文件: examples/example-wrapper.php

```php
mkdir("scs://{$bucketName}");

file_put_contents("scs://{$bucketName}/test.txt", "http://weibo.com/smcz !");

file_get_contents("scs://{$bucketName}/test.txt")

foreach (new DirectoryIterator("scs://{$bucketName}") as $b) {

	echo "\t" . $b . "\n";
}

unlink("scs://{$bucketName}/test.txt");

rmdir("scs://{$bucketName}");
```

#### 大文件分片上传

* 文件: examples/example-multipart-upload.php

```php
$fp = fopen($file, 'rb');
SCS::setExceptions(true);

try
{
	//初始化上传
	$info = SCS::initiateMultipartUpload($bucket, $object, SCS::ACL_PUBLIC_READ);
	$uploadId = $info['upload_id'];
	$fp = fopen($file, 'rb');
	$i = 1;
	$part_info = array();
	
	while (!feof($fp)) {
		
		//上传分片	
		$res = SCS::putObject(SCS::inputResourceMultipart($fp, 1024*512, $uploadId, $i), $bucket, $object);	
		if (isset($res['hash']))
		{	
			echo 'Part: ' . $i . " OK! \n";
			
			$part_info[] = array(
				
				'PartNumber' => $i,
				'ETag' => $res['hash'],
			);
		}
		$i++;
	}
	
	//列分片
	$parts = SCS::listParts($bucket, $object, $uploadId);
	//print_r($parts);
	//print_r($part_info);
	
	if (count($parts) > 0 && count($parts) == count($part_info)) {
		
		foreach ($parts as $part_number => $part) {
			
			//echo $part['etag'] . "\n";
			//echo $part_info[$k]['ETag'] . "\n";
			
			if ($part['etag'] != $part_info[$part_number-1]['ETag']) {
				
				exit('分片不匹配');
				break;
			}
		}
		
		//合并分片
		echo "开始合并\n";
		SCS::completeMultipartUpload($bucket, $object, $uploadId, $part_info);
		echo "上传完成\n";	
		fclose($fp);
	}
}
catch(SCSException $e)
{
    echo $e->getMessage();
}
```
