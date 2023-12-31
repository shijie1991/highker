<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace HighKer\Core\Support\FileDriver\scs\class;

use DOMDocument;
use Exception;
use stdClass;

/**
 * Sina Cloud Storage PHP class.
 *
 * @see http://weibo.com/smcz
 *
 * @version 0.1.0-dev
 */
class SCS
{
    // ACL flags
    public const ACL_PRIVATE = 'private';
    public const ACL_PUBLIC_READ = 'public-read';
    public const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    public const ACL_AUTHENTICATED_READ = 'authenticated-read';

    public const STORAGE_CLASS_STANDARD = 'STANDARD';
    public const STORAGE_CLASS_RRS = 'REDUCED_REDUNDANCY';

    public const SSE_NONE = '';
    public const SSE_AES256 = 'AES256';

    /**
     * The SCS Access key.
     *
     * @var string
     *
     * @static
     */
    private static $__accessKey;

    /**
     * SCS Secret Key.
     *
     * @var string
     *
     * @static
     */
    private static $__secretKey;

    /**
     * SSL Client key.
     *
     * @var string
     *
     * @static
     */
    private static $__sslKey;

    /**
     * SCS URI.
     *
     * @var string
     *
     * @acess public
     *
     * @static
     */
    public static $endpoint = 'sinacloud.net';

    /**
     * Proxy information.
     *
     * @var null|array
     *
     * @static
     */
    public static $proxy;

    /**
     * Connect using SSL?
     *
     * @var bool
     *
     * @static
     */
    public static $useSSL = false;

    /**
     * Use SSL validation?
     *
     * @var bool
     *
     * @static
     */
    public static $useSSLValidation = true;

    /**
     * Use PHP exceptions?
     *
     * @var bool
     *
     * @static
     */
    public static $useExceptions = false;

    /**
     * Time offset applied to time().
     *
     * @static
     */
    private static $__timeOffset = 5;

    /**
     * SSL client key.
     *
     * @var bool
     *
     * @static
     */
    public static $sslKey;

    /**
     * SSL client certfificate.
     *
     * @var string
     *
     * @acess public
     *
     * @static
     */
    public static $sslCert;

    /**
     * SSL CA cert (only required if you are having problems with your system CA cert).
     *
     * @var string
     *
     * @static
     */
    public static $sslCACert;

    /**
     * SCS Key Pair ID.
     *
     * @var string
     *
     * @static
     */
    private static $__signingKeyPairId;

    /**
     * Key resource, freeSigningKey() must be called to clear it from memory.
     *
     * @var bool
     *
     * @static
     */
    private static $__signingKeyResource = false;

    /**
     * Constructor - if you're not using the class statically.
     *
     * @param string $accessKey Access key
     * @param string $secretKey Secret key
     * @param bool   $useSSL    Enable SSL
     * @param string $endpoint  Amazon URI
     */
    public function __construct($accessKey = null, $secretKey = null, $useSSL = false, $endpoint = 'sinacloud.net')
    {
        if ($accessKey !== null && $secretKey !== null) {
            self::setAuth($accessKey, $secretKey);
        }
        self::$useSSL = $useSSL;
        self::$endpoint = $endpoint;
    }

    /**
     * Set the service endpoint.
     *
     * @param string $host Hostname
     */
    public function setEndpoint($host)
    {
        self::$endpoint = $host;
    }

    /**
     * Set SCS access key and secret key.
     *
     * @param string $accessKey Access key
     * @param string $secretKey Secret key
     */
    public static function setAuth($accessKey, $secretKey)
    {
        self::$__accessKey = $accessKey;
        self::$__secretKey = $secretKey;
    }

    /**
     * Check if SCS keys have been set.
     *
     * @return bool
     */
    public static function hasAuth()
    {
        return self::$__accessKey !== null && self::$__secretKey !== null;
    }

    /**
     * Set SSL on or off.
     *
     * @param bool $enabled  SSL enabled
     * @param bool $validate SSL certificate validation
     */
    public static function setSSL($enabled, $validate = true)
    {
        self::$useSSL = $enabled;
        self::$useSSLValidation = $validate;
    }

    /**
     * Set SSL client certificates (experimental).
     *
     * @param string $sslCert   SSL client certificate
     * @param string $sslKey    SSL client key
     * @param string $sslCACert SSL CA cert (only required if you are having problems with your system CA cert)
     */
    public static function setSSLAuth($sslCert = null, $sslKey = null, $sslCACert = null)
    {
        self::$sslCert = $sslCert;
        self::$sslKey = $sslKey;
        self::$sslCACert = $sslCACert;
    }

    /**
     * Set proxy information.
     *
     * @param string   $host Proxy hostname and port (localhost:1234)
     * @param string   $user Proxy username
     * @param string   $pass Proxy password
     * @param constant $type CURL proxy type
     */
    public static function setProxy($host, $user = null, $pass = null, $type = CURLPROXY_SOCKS5)
    {
        self::$proxy = ['host' => $host, 'type' => $type, 'user' => $user, 'pass' => $pass];
    }

    /**
     * Set the error mode to exceptions.
     *
     * @param bool $enabled Enable exceptions
     */
    public static function setExceptions($enabled = true)
    {
        self::$useExceptions = $enabled;
    }

    /**
     * Set SCS time correction offset (use carefully).
     *
     * This can be used when an inaccurate system time is generating
     * invalid request signatures.  It should only be used as a last
     * resort when the system time cannot be changed.
     *
     * @param string $offset Time offset (set to zero to use SCS server time)
     */
    public static function setTimeCorrectionOffset($offset = 0)
    {
        if ($offset == 0) {
            $rest = new SCSRequest('HEAD');
            $rest = $rest->getResponse();
            $awstime = $rest->headers['date'];
            $systime = time();
            $offset = $systime > $awstime ? -($systime - $awstime) : ($awstime - $systime);
        }
        self::$__timeOffset = $offset;
    }

    /**
     * Set signing key.
     *
     * @param string $keyPairId  SCS Key Pair ID
     * @param string $signingKey Private Key
     * @param bool   $isFile     Load private key from file, set to false to load string
     *
     * @return bool
     */
    public static function setSigningKey($keyPairId, $signingKey, $isFile = true)
    {
        self::$__signingKeyPairId = $keyPairId;
        if ((self::$__signingKeyResource = openssl_pkey_get_private($isFile ?
        file_get_contents($signingKey) : $signingKey)) !== false) {
            return true;
        }
        self::__triggerError('SCS::setSigningKey(): Unable to open load private key: '.$signingKey, __FILE__, __LINE__);

        return false;
    }

    /**
     * Free signing key from memory, MUST be called if you are using setSigningKey().
     */
    public static function freeSigningKey()
    {
        if (self::$__signingKeyResource !== false) {
            openssl_free_key(self::$__signingKeyResource);
        }
    }

    /**
     * Internal error handler.
     *
     * @internal Internal error handler
     *
     * @param string $message Error message
     * @param string $file    Filename
     * @param int    $line    Line number
     * @param int    $code    Error code
     */
    private static function __triggerError($message, $file, $line, $code = 0)
    {
        if (self::$useExceptions) {
            throw new SCSException($message, $file, $line, $code);
        } else {
            trigger_error($message, E_USER_WARNING);
        }
    }

    /**
     * Get a list of buckets.
     *
     * @param bool $detailed Returns detailed bucket list when true
     *
     * @return array|false
     */
    public static function listBuckets($detailed = false)
    {
        $rest = new SCSRequest('GET', '', '', self::$endpoint);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                'SCS::listBuckets(): [%s] %s',
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }
        $results = [];
        if (!isset($rest->body->Buckets)) {
            return $results;
        }

        if ($detailed) {
            if (isset($rest->body->Owner, $rest->body->Owner->ID, $rest->body->Owner->DisplayName)) {
                $results['owner'] = [
                    'id' => (string) $rest->body->Owner->ID, 'name' => (string) $rest->body->Owner->DisplayName,
                ];
            }
            $results['buckets'] = [];
            // foreach ($rest->body->Buckets->Bucket as $b)
            foreach ($rest->body->Buckets as $b) {
                $results['buckets'][] = [
                    'name' => (string) $b->Name, 'time' => strtotime((string) $b->CreationDate), 'consumed_bytes' => (string) $b->ConsumedBytes,
                ];
            }
        } else {
            foreach ($rest->body->Buckets as $b) {
                $results[] = (string) $b->Name;
            }
        }
        // foreach ($rest->body->Buckets->Bucket as $b) $results[] = (string)$b->Name;

        return $results;
    }

    /**
     * Get contents for a bucket.
     *
     * If maxKeys is null this method will loop through truncated result sets
     *
     * @param string     $bucket               Bucket name
     * @param string     $prefix               Prefix
     * @param string     $marker               Marker (last file listed)
     * @param string     $maxKeys              Max keys (maximum number of keys to return)
     * @param string     $delimiter            Delimiter
     * @param bool       $returnCommonPrefixes Set to true to return CommonPrefixes
     * @param bool       &$isTruncated
     * @param null|mixed $nextMarker
     *
     * @return array|false
     */
    public static function getBucket($bucket, $prefix = null, $marker = null, $maxKeys = null, $delimiter = null, $returnCommonPrefixes = false, &$nextMarker = null, &$isTruncated = false)
    {
        $rest = new SCSRequest('GET', $bucket, '', self::$endpoint);
        if ($maxKeys == 0) {
            $maxKeys = null;
        }
        if ($prefix !== null && $prefix !== '') {
            $rest->setParameter('prefix', $prefix);
        }
        if ($marker !== null && $marker !== '') {
            $rest->setParameter('marker', $marker);
        }
        if ($maxKeys !== null && $maxKeys !== '') {
            $rest->setParameter('max-keys', $maxKeys);
        }
        if ($delimiter !== null && $delimiter !== '') {
            $rest->setParameter('delimiter', $delimiter);
        }
        $response = $rest->getResponse();
        if ($response->error === false && $response->code !== 200) {
            $response->error = ['code' => $response->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($response->error !== false) {
            self::__triggerError(sprintf(
                'SCS::getBucket(): [%s] %s',
                $response->error['code'],
                $response->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        $results = [];

        if ($returnCommonPrefixes && isset($response->body, $response->body->CommonPrefixes)) {
            foreach ($response->body->CommonPrefixes as $c) {
                $results[(string) $c->Prefix] = ['prefix' => (string) $c->Prefix];
            }
        }

        $nextMarker = null;

        if (isset($response->body, $response->body->Contents)) {
            foreach ($response->body->Contents as $c) {
                $a = get_object_vars($c);

                $results[(string) $c->Name] = [
                    'name'  => (string) $c->Name,
                    'time'  => strtotime((string) $a['Last-Modified']),
                    'type'  => (string) $a['Content-Type'],
                    'owner' => (string) $a['Owner'],
                    'size'  => (int) $c->Size,
                    // 'hash' => substr((string)$c->ETag, 1, -1),
                    'md5'  => (string) $c->MD5,
                    'sha1' => (string) $c->SHA1,
                ];

                $nextMarker = (string) $c->Name;
            }
        }

        if (isset($response->body, $response->body->IsTruncated)) {
            $isTruncated = (bool) $response->body->IsTruncated;
        }

        if (isset($response->body, $response->body->IsTruncated) && (bool) $response->body->IsTruncated == false) {
            return $results;
        }

        if (isset($response->body, $response->body->NextMarker)) {
            $nextMarker = (string) $response->body->NextMarker;
        }

        // Loop through truncated results if maxKeys isn't specified
        if ($maxKeys == null && $nextMarker !== null && (bool) $response->body->IsTruncated == true) {
            do {
                $rest = new SCSRequest('GET', $bucket, '', self::$endpoint);

                if ($prefix !== null && $prefix !== '') {
                    $rest->setParameter('prefix', $prefix);
                }

                $rest->setParameter('marker', $nextMarker);

                if ($delimiter !== null && $delimiter !== '') {
                    $rest->setParameter('delimiter', $delimiter);
                }

                if (($response = $rest->getResponse()) == false || $response->code !== 200) {
                    break;
                }

                if ($returnCommonPrefixes && isset($response->body, $response->body->CommonPrefixes)) {
                    foreach ($response->body->CommonPrefixes as $c) {
                        $results[(string) $c->Prefix] = ['prefix' => (string) $c->Prefix];
                    }
                }

                if (isset($response->body, $response->body->Contents)) {
                    foreach ($response->body->Contents as $c) {
                        $a = get_object_vars($c);

                        $results[(string) $c->Name] = [
                            'name'  => (string) $c->Name,
                            'time'  => strtotime((string) $a['Last-Modified']),
                            'type'  => (string) $a['Content-Type'],
                            'owner' => (string) $a['Owner'],
                            'size'  => (int) $c->Size,
                            // 'hash' => substr((string)$c->ETag, 1, -1),
                            'md5'  => (string) $c->MD5,
                            'sha1' => (string) $c->SHA1,
                        ];

                        $nextMarker = (string) $c->Name;
                    }
                }

                if (isset($response->body, $response->body->NextMarker)) {
                    $nextMarker = (string) $response->body->NextMarker;
                }
            } while ($response !== false && (bool) $response->body->IsTruncated == true);
        }

        return $results;
    }

    /**
     * Put a bucket.
     *
     * @param string   $bucket   Bucket name
     * @param constant $acl      ACL flag
     * @param string   $location Set as "EU" to create buckets hosted in Europe
     *
     * @return bool
     */
    public static function putBucket($bucket, $acl = self::ACL_PRIVATE, $location = false)
    {
        $rest = new SCSRequest('PUT', $bucket, '', self::$endpoint);
        $rest->setAmzHeader('x-amz-acl', $acl);

        if ($location !== false) {
            $dom = new DOMDocument();
            $createBucketConfiguration = $dom->createElement('CreateBucketConfiguration');
            $locationConstraint = $dom->createElement('LocationConstraint', $location);
            $createBucketConfiguration->appendChild($locationConstraint);
            $dom->appendChild($createBucketConfiguration);
            $rest->data = $dom->saveXML();
            $rest->size = strlen($rest->data);
            $rest->setHeader('Content-Type', 'application/xml');
        }
        $rest = $rest->getResponse();

        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::putBucket({$bucket}, {$acl}, {$location}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Delete an empty bucket.
     *
     * @param string $bucket Bucket name
     *
     * @return bool
     */
    public static function deleteBucket($bucket)
    {
        $rest = new SCSRequest('DELETE', $bucket, '', self::$endpoint);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 204) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::deleteBucket({$bucket}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * realFileSize().
     *
     * @param string $file Input file path
     *
     * @return long file bytes
     */
    public static function realFileSize($file)
    {
        $fp = fopen($file, 'rb');

        $pos = 0;
        $size = 1073741824;
        fseek($fp, 0, SEEK_SET);
        while ($size > 1) {
            fseek($fp, $size, SEEK_CUR);

            if (fgetc($fp) === false) {
                fseek($fp, -$size, SEEK_CUR);
                $size = (int) ($size / 2);
            } else {
                fseek($fp, -1, SEEK_CUR);
                $pos += $size;
            }
        }

        while (fgetc($fp) !== false) {
            ++$pos;
        }

        fclose($fp);

        return $pos;
    }

    /**
     * Create input info array for putObject().
     *
     * @param string $file   Input file
     * @param mixed  $md5sum Use MD5 hash (supply a string if you want to use your own)
     *
     * @return array|false
     */
    public static function inputFile($file, $md5sum = true)
    {
        if (!file_exists($file) || !is_file($file) || !is_readable($file)) {
            self::__triggerError('SCS::inputFile(): Unable to open input file: '.$file, __FILE__, __LINE__);

            return false;
        }

        return ['file' => $file, 'size' => self::realFileSize($file), 'md5sum' => $md5sum !== false ?
        (is_string($md5sum) ? $md5sum : base64_encode(md5_file($file, true))) : '', ];
    }

    /**
     * Create input array info for putObject() with a resource.
     *
     * @param string $resource   Input resource to read from
     * @param int    $bufferSize Input byte size
     * @param string $md5sum     MD5 hash to send (optional)
     *
     * @return array|false
     */
    public static function inputResource(&$resource, $bufferSize = false, $md5sum = '')
    {
        if (!is_resource($resource) || (int) $bufferSize < 0) {
            self::__triggerError('SCS::inputResource(): Invalid resource or buffer size', __FILE__, __LINE__);

            return false;
        }

        // Try to figure out the bytesize
        if ($bufferSize === false) {
            if (fseek($resource, 0, SEEK_END) < 0 || ($bufferSize = ftell($resource)) === false) {
                self::__triggerError('SCS::inputResource(): Unable to obtain resource size', __FILE__, __LINE__);

                return false;
            }
            fseek($resource, 0);
        }

        $input = ['size' => $bufferSize, 'md5sum' => $md5sum];
        $input['fp'] = &$resource;

        return $input;
    }

    /**
     * Create input array info for putObject() with a resource multipart.
     *
     * @param string $resource   Input resource to read from
     * @param int    $partSize
     * @param string $uploadId
     * @param int    $partNumber
     *
     * @return array|false
     */
    public static function inputResourceMultipart(&$resource, $partSize, $uploadId, $partNumber)
    {
        if (!is_resource($resource) || (int) $partSize <= 0) {
            self::__triggerError('SCS::inputResourceMultipart(): Invalid resource or part size', __FILE__, __LINE__);

            return false;
        }

        $data = fread($resource, $partSize);

        $input = [
            'data'   => $data,
            'md5sum' => base64_encode(md5($data, true)),
        ];

        $input['uploadId'] = $uploadId;
        $input['partNumber'] = $partNumber;

        return $input;
    }

    /**
     * Put an object.
     *
     * @param mixed    $input                Input data
     * @param string   $bucket               Bucket name
     * @param string   $uri                  Object URI
     * @param constant $acl                  ACL constant
     * @param array    $metaHeaders          Array of x-amz-meta-* headers
     * @param array    $requestHeaders       Array of request headers or content type as a string
     * @param constant $storageClass         Storage class constant
     * @param constant $serverSideEncryption Server-side encryption
     *
     * @return bool|mixed
     */
    public static function putObject($input, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = [], $requestHeaders = [], $storageClass = self::STORAGE_CLASS_STANDARD, $serverSideEncryption = self::SSE_NONE)
    {
        if ($input === false) {
            return false;
        }
        $rest = new SCSRequest('PUT', $bucket, $uri, self::$endpoint);

        if (!is_array($input)) {
            $input = [
                'data'   => $input, 'size' => strlen($input),
                'md5sum' => base64_encode(md5($input, true)),
            ];
        }

        // Data
        if (isset($input['fp'])) {
            $rest->fp = &$input['fp'];
        } elseif (isset($input['file'])) {
            $rest->fp = @fopen($input['file'], 'rb');
        } elseif (isset($input['data'])) {
            $rest->data = $input['data'];
        }

        // Content-Length (required)
        if (isset($input['size']) && $input['size'] >= 0) {
            $rest->size = $input['size'];
        } else {
            if (isset($input['file'])) {
                $rest->size = self::realFileSize($input['file']);
            } elseif (isset($input['data'])) {
                $rest->size = strlen($input['data']);
            }
        }

        if (isset($input['uploadId'], $input['partNumber'])) {
            $rest->setParameter('uploadId', $input['uploadId']);
            $rest->setParameter('partNumber', $input['partNumber']);
        }

        // Custom request headers (Content-Type, Content-Disposition, Content-Encoding)
        if (is_array($requestHeaders)) {
            foreach ($requestHeaders as $h => $v) {
                $rest->setHeader($h, $v);
            }
        } elseif (is_string($requestHeaders)) { // Support for legacy contentType parameter
            $input['type'] = $requestHeaders;
        }

        // Content-Type
        if (!isset($input['type'])) {
            if (isset($requestHeaders['Content-Type'])) {
                $input['type'] = &$requestHeaders['Content-Type'];
            } elseif (isset($input['file'])) {
                $input['type'] = self::__getMIMEType($input['file']);
            } else {
                $input['type'] = 'application/octet-stream';
            }
        }

        if ($storageClass !== self::STORAGE_CLASS_STANDARD) { // Storage class
            $rest->setAmzHeader('x-amz-storage-class', $storageClass);
        }

        if ($serverSideEncryption !== self::SSE_NONE) { // Server-side encryption
            $rest->setAmzHeader('x-amz-server-side-encryption', $serverSideEncryption);
        }

        // We need to post with Content-Length and Content-Type, MD5 is optional
        if ($rest->size >= 0 && ($rest->fp !== false || $rest->data !== false)) {
            $rest->setHeader('Content-Type', $input['type']);
            if (isset($input['md5sum'])) {
                $rest->setHeader('Content-MD5', $input['md5sum']);
            }

            $rest->setAmzHeader('x-amz-acl', $acl);
            foreach ($metaHeaders as $h => $v) {
                $rest->setAmzHeader('x-amz-meta-'.$h, $v);
            }
            $rest->getResponse();
        } else {
            $rest->response->error = ['code' => 0, 'message' => 'Missing input parameters'];
        }

        if ($rest->response->error === false && $rest->response->code !== 200) {
            $rest->response->error = ['code' => $rest->response->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->response->error !== false) {
            self::__triggerError(sprintf(
                'SCS::putObject(): [%s] %s',
                $rest->response->error['code'],
                $rest->response->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        if (isset($input['uploadId'], $input['partNumber'])) {
            return $rest->response->headers;
        }

        return true;
    }

    /**
     * Put an object from a file (legacy function).
     *
     * @param string   $file        Input file path
     * @param string   $bucket      Bucket name
     * @param string   $uri         Object URI
     * @param constant $acl         ACL constant
     * @param array    $metaHeaders Array of x-amz-meta-* headers
     * @param string   $contentType Content type
     *
     * @return bool
     */
    public static function putObjectFile($file, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = [], $contentType = null)
    {
        return self::putObject(self::inputFile($file), $bucket, $uri, $acl, $metaHeaders, $contentType);
    }

    /**
     * Put an object from a string (legacy function).
     *
     * @param string   $string      Input data
     * @param string   $bucket      Bucket name
     * @param string   $uri         Object URI
     * @param constant $acl         ACL constant
     * @param array    $metaHeaders Array of x-amz-meta-* headers
     * @param string   $contentType Content type
     *
     * @return bool
     */
    public static function putObjectString($string, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = [], $contentType = 'text/plain')
    {
        return self::putObject($string, $bucket, $uri, $acl, $metaHeaders, $contentType);
    }

    /**
     * Get an object.
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     * @param mixed  $saveTo Filename or resource to write to
     */
    public static function getObject($bucket, $uri, $saveTo = false)
    {
        $rest = new SCSRequest('GET', $bucket, $uri, self::$endpoint);
        if ($saveTo !== false) {
            if (is_resource($saveTo)) {
                $rest->fp = &$saveTo;
            } elseif (($rest->fp = @fopen($saveTo, 'wb')) !== false) {
                $rest->file = realpath($saveTo);
            } else {
                $rest->response->error = ['code' => 0, 'message' => 'Unable to open save file for writing: '.$saveTo];
            }
        }
        if ($rest->response->error === false) {
            $rest->getResponse();
        }

        if ($rest->response->error === false && $rest->response->code !== 200) {
            $rest->response->error = ['code' => $rest->response->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->response->error !== false) {
            self::__triggerError(sprintf(
                "SCS::getObject({$bucket}, {$uri}): [%s] %s",
                $rest->response->error['code'],
                $rest->response->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return $rest->response;
    }

    /**
     * Get object information.
     *
     * @param string $bucket     Bucket name
     * @param string $uri        Object URI
     * @param bool   $returnInfo Return response information
     *
     * @return false|mixed
     */
    public static function getObjectInfo($bucket, $uri, $returnInfo = true)
    {
        $rest = new SCSRequest('HEAD', $bucket, $uri, self::$endpoint);
        $rest = $rest->getResponse();
        if ($rest->error === false && ($rest->code !== 200 && $rest->code !== 404)) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::getObjectInfo({$bucket}, {$uri}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return $rest->code == 200 ? $returnInfo ? $rest->headers : true : false;
    }

    /**
     * Get Meta.
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     *
     * @return false|mixed
     */
    public static function getMeta($bucket, $uri = '')
    {
        $rest = new SCSRequest('GET', $bucket, $uri, self::$endpoint);
        $rest->setParameter('meta', null);
        $rest = $rest->getResponse();
        if ($rest->error === false && ($rest->code !== 200 && $rest->code !== 404)) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::getMeta({$bucket}, {$uri}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return $rest->code == 200 ? $rest->body : false;
    }

    /**
     * Copy an object.
     *
     * @param string   $srcBucket      Source bucket name
     * @param string   $srcUri         Source object URI
     * @param string   $bucket         Destination bucket name
     * @param string   $uri            Destination object URI
     * @param constant $acl            ACL constant
     * @param array    $metaHeaders    Optional array of x-amz-meta-* headers
     * @param array    $requestHeaders Optional array of request headers (content type, disposition, etc.)
     * @param constant $storageClass   Storage class constant
     *
     * @return false|mixed
     */
    public static function copyObject($srcBucket, $srcUri, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = [], $requestHeaders = [], $storageClass = self::STORAGE_CLASS_STANDARD)
    {
        $rest = new SCSRequest('PUT', $bucket, $uri, self::$endpoint);
        $rest->setHeader('Content-Length', 0);
        foreach ($requestHeaders as $h => $v) {
            $rest->setHeader($h, $v);
        }
        foreach ($metaHeaders as $h => $v) {
            $rest->setAmzHeader('x-amz-meta-'.$h, $v);
        }
        if ($storageClass !== self::STORAGE_CLASS_STANDARD) { // Storage class
            $rest->setAmzHeader('x-amz-storage-class', $storageClass);
        }
        $rest->setAmzHeader('x-amz-acl', $acl);
        $rest->setAmzHeader('x-amz-copy-source', sprintf('/%s/%s', $srcBucket, rawurlencode($srcUri)));
        // $rest->setAmzHeader('x-amz-copy-source', sprintf('/%s/%s', $srcBucket, $srcUri));
        if (sizeof($requestHeaders) > 0 || sizeof($metaHeaders) > 0) {
            $rest->setAmzHeader('x-amz-metadata-directive', 'REPLACE');
        }

        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::copyObject({$srcBucket}, {$srcUri}, {$bucket}, {$uri}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Relax an object 秒传.
     *
     * @param string   $bucket         Destination bucket name
     * @param string   $uri            Destination object URI
     * @param string   $sha1           Destination object sha1
     * @param int64    $size           Destination object size
     * @param constant $acl            ACL constant
     * @param array    $metaHeaders    Optional array of x-amz-meta-* headers
     * @param array    $requestHeaders Optional array of request headers (content type, disposition, etc.)
     * @param constant $storageClass   Storage class constant
     *
     * @return false|mixed
     */
    public static function putObjectRelax($bucket, $uri, $sha1, $size, $acl = self::ACL_PRIVATE, $metaHeaders = [], $requestHeaders = [], $storageClass = self::STORAGE_CLASS_STANDARD)
    {
        $rest = new SCSRequest('PUT', $bucket, $uri, self::$endpoint);

        $rest->setParameter('relax', null);

        $rest->setHeader('Content-Length', 0);
        $rest->setHeader('s-sina-sha1', $sha1);
        $rest->setHeader('s-sina-length', $size);

        foreach ($requestHeaders as $h => $v) {
            $rest->setHeader($h, $v);
        }
        foreach ($metaHeaders as $h => $v) {
            $rest->setAmzHeader('x-amz-meta-'.$h, $v);
        }

        if ($storageClass !== self::STORAGE_CLASS_STANDARD) { // Storage class
            $rest->setAmzHeader('x-amz-storage-class', $storageClass);
        }

        $rest->setAmzHeader('x-amz-acl', $acl);

        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::putObjectRelax({$bucket}, {$uri}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return $rest->headers;
    }

    /**
     * Set up a bucket redirection.
     *
     * @param string $bucket   Bucket name
     * @param string $location Target host name
     *
     * @return bool
     */
    public static function setBucketRedirect($bucket = null, $location = null)
    {
        $rest = new SCSRequest('PUT', $bucket, '', self::$endpoint);

        if (empty($bucket) || empty($location)) {
            self::__triggerError("SCS::setBucketRedirect({$bucket}, {$location}): Empty parameter.", __FILE__, __LINE__);

            return false;
        }

        $dom = new DOMDocument();
        $websiteConfiguration = $dom->createElement('WebsiteConfiguration');
        $redirectAllRequestsTo = $dom->createElement('RedirectAllRequestsTo');
        $hostName = $dom->createElement('HostName', $location);
        $redirectAllRequestsTo->appendChild($hostName);
        $websiteConfiguration->appendChild($redirectAllRequestsTo);
        $dom->appendChild($websiteConfiguration);
        $rest->setParameter('website', null);
        $rest->data = $dom->saveXML();
        $rest->size = strlen($rest->data);
        $rest->setHeader('Content-Type', 'application/xml');
        $rest = $rest->getResponse();

        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::setBucketRedirect({$bucket}, {$location}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Set logging for a bucket.
     *
     * @param string $bucket       Bucket name
     * @param string $targetBucket Target bucket (where logs are stored)
     * @param string $targetPrefix Log prefix (e,g; domain.com-)
     *
     * @return bool
     */
    public static function setBucketLogging($bucket, $targetBucket, $targetPrefix = null)
    {
        // The SCS log delivery group has to be added to the target bucket's ACP
        if ($targetBucket !== null && ($acp = self::getAccessControlPolicy($targetBucket, '')) !== false) {
            // Only add permissions to the target bucket when they do not exist
            $aclWriteSet = false;
            $aclReadSet = false;
            foreach ($acp['acl'] as $acl) {
                if ($acl['type'] == 'Group' && $acl['uri'] == 'http://acs.amazonaws.com/groups/s3/LogDelivery') {
                    if ($acl['permission'] == 'WRITE') {
                        $aclWriteSet = true;
                    } elseif ($acl['permission'] == 'READ_ACP') {
                        $aclReadSet = true;
                    }
                }
            }
            if (!$aclWriteSet) {
                $acp['acl'][] = [
                    'type' => 'Group', 'uri' => 'http://acs.amazonaws.com/groups/s3/LogDelivery', 'permission' => 'WRITE',
                ];
            }
            if (!$aclReadSet) {
                $acp['acl'][] = [
                    'type' => 'Group', 'uri' => 'http://acs.amazonaws.com/groups/s3/LogDelivery', 'permission' => 'READ_ACP',
                ];
            }
            if (!$aclReadSet || !$aclWriteSet) {
                self::setAccessControlPolicy($targetBucket, '', $acp);
            }
        }

        $dom = new DOMDocument();
        $bucketLoggingStatus = $dom->createElement('BucketLoggingStatus');
        $bucketLoggingStatus->setAttribute('xmlns', 'http://s3.amazonaws.com/doc/2006-03-01/');
        if ($targetBucket !== null) {
            if ($targetPrefix == null) {
                $targetPrefix = $bucket.'-';
            }
            $loggingEnabled = $dom->createElement('LoggingEnabled');
            $loggingEnabled->appendChild($dom->createElement('TargetBucket', $targetBucket));
            $loggingEnabled->appendChild($dom->createElement('TargetPrefix', $targetPrefix));
            // TODO: Add TargetGrants?
            $bucketLoggingStatus->appendChild($loggingEnabled);
        }
        $dom->appendChild($bucketLoggingStatus);

        $rest = new SCSRequest('PUT', $bucket, '', self::$endpoint);
        $rest->setParameter('logging', null);
        $rest->data = $dom->saveXML();
        $rest->size = strlen($rest->data);
        $rest->setHeader('Content-Type', 'application/xml');
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::setBucketLogging({$bucket}, {$targetBucket}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Get logging status for a bucket.
     *
     * This will return false if logging is not enabled.
     * Note: To enable logging, you also need to grant write access to the log group
     *
     * @param string $bucket Bucket name
     *
     * @return array|false
     */
    public static function getBucketLogging($bucket)
    {
        $rest = new SCSRequest('GET', $bucket, '', self::$endpoint);
        $rest->setParameter('logging', null);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::getBucketLogging({$bucket}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }
        if (!isset($rest->body->LoggingEnabled)) {
            return false;
        } // No logging

        return [
            'targetBucket' => (string) $rest->body->LoggingEnabled->TargetBucket,
            'targetPrefix' => (string) $rest->body->LoggingEnabled->TargetPrefix,
        ];
    }

    /**
     * Disable bucket logging.
     *
     * @param string $bucket Bucket name
     *
     * @return bool
     */
    public static function disableBucketLogging($bucket)
    {
        return self::setBucketLogging($bucket, null);
    }

    /**
     * Get a bucket's location.
     *
     * @param string $bucket Bucket name
     *
     * @return false|string
     */
    public static function getBucketLocation($bucket)
    {
        $rest = new SCSRequest('GET', $bucket, '', self::$endpoint);
        $rest->setParameter('location', null);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::getBucketLocation({$bucket}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return (isset($rest->body[0]) && (string) $rest->body[0] !== '') ? (string) $rest->body[0] : 'US';
    }

    /**
     * Set object or bucket Access Control Policy.
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     * @param array  $acp    Access Control Policy Data (same as the data returned from getAccessControlPolicy)
     *
     * @return bool
     */
    public static function setAccessControlPolicy($bucket, $uri = '', $acp = [])
    {
        /*
        $dom = new DOMDocument;
        $dom->formatOutput = true;
        $accessControlPolicy = $dom->createElement('AccessControlPolicy');
        $accessControlList = $dom->createElement('AccessControlList');

        // It seems the owner has to be passed along too
        $owner = $dom->createElement('Owner');
        $owner->appendChild($dom->createElement('ID', $acp['owner']['id']));
        $owner->appendChild($dom->createElement('DisplayName', $acp['owner']['name']));
        $accessControlPolicy->appendChild($owner);

        foreach ($acp['acl'] as $g)
        {
            $grant = $dom->createElement('Grant');
            $grantee = $dom->createElement('Grantee');
            $grantee->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            if (isset($g['id']))
            { // CanonicalUser (DisplayName is omitted)
                $grantee->setAttribute('xsi:type', 'CanonicalUser');
                $grantee->appendChild($dom->createElement('ID', $g['id']));
            }
            elseif (isset($g['email']))
            { // AmazonCustomerByEmail
                $grantee->setAttribute('xsi:type', 'AmazonCustomerByEmail');
                $grantee->appendChild($dom->createElement('EmailAddress', $g['email']));
            }
            elseif ($g['type'] == 'Group')
            { // Group
                $grantee->setAttribute('xsi:type', 'Group');
                $grantee->appendChild($dom->createElement('URI', $g['uri']));
            }
            $grant->appendChild($grantee);
            $grant->appendChild($dom->createElement('Permission', $g['permission']));
            $accessControlList->appendChild($grant);
        }

        $accessControlPolicy->appendChild($accessControlList);
        $dom->appendChild($accessControlPolicy);
        */

        $rest = new SCSRequest('PUT', $bucket, $uri, self::$endpoint);
        $rest->setParameter('acl', null);
        // $rest->data = $dom->saveXML();
        $rest->data = json_encode($acp);
        $rest->size = strlen($rest->data);
        // $rest->setHeader('Content-Type', 'application/xml');
        $rest->setHeader('Content-Type', 'application/json');
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::setAccessControlPolicy({$bucket}, {$uri}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Get object or bucket Access Control Policy.
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     *
     * @return false|mixed
     */
    public static function getAccessControlPolicy($bucket, $uri = '')
    {
        $rest = new SCSRequest('GET', $bucket, $uri, self::$endpoint);
        $rest->setParameter('acl', null);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::getAccessControlPolicy({$bucket}, {$uri}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        $acp = [];

        /*
        if (isset($rest->body->Owner, $rest->body->Owner->ID, $rest->body->Owner->DisplayName))
            $acp['owner'] = array(
                'id' => (string)$rest->body->Owner->ID, 'name' => (string)$rest->body->Owner->DisplayName
            );

        if (isset($rest->body->AccessControlList))
        {
            $acp['acl'] = array();
            foreach ($rest->body->AccessControlList->Grant as $grant)
            {
                foreach ($grant->Grantee as $grantee)
                {
                    if (isset($grantee->ID, $grantee->DisplayName)) // CanonicalUser
                        $acp['acl'][] = array(
                            'type' => 'CanonicalUser',
                            'id' => (string)$grantee->ID,
                            'name' => (string)$grantee->DisplayName,
                            'permission' => (string)$grant->Permission
                        );
                    elseif (isset($grantee->EmailAddress)) // AmazonCustomerByEmail
                        $acp['acl'][] = array(
                            'type' => 'AmazonCustomerByEmail',
                            'email' => (string)$grantee->EmailAddress,
                            'permission' => (string)$grant->Permission
                        );
                    elseif (isset($grantee->URI)) // Group
                        $acp['acl'][] = array(
                            'type' => 'Group',
                            'uri' => (string)$grantee->URI,
                            'permission' => (string)$grant->Permission
                        );
                    else continue;
                }
            }
        }
        */

        if (isset($rest->body->Owner)) {
            $acp['owner'] = $rest->body->Owner;
        }

        if (isset($rest->body->ACL)) {
            $acp['acl'] = get_object_vars($rest->body->ACL);
        }

        return $acp;
    }

    /**
     * Delete an object.
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     *
     * @return bool
     *
     * @throws SCSException
     */
    public static function deleteObject($bucket, $uri)
    {
        $rest = new SCSRequest('DELETE', $bucket, $uri, self::$endpoint);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 204) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                'SCS::deleteObject(): [%s] %s',
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Get a query string authenticated URL.
     *
     * @param string $bucket       Bucket name
     * @param string $uri          Object URI
     * @param int    $lifetime     Lifetime in seconds
     * @param bool   $hostBucket   Use the bucket name as the hostname
     * @param bool   $https        Use HTTPS ($hostBucket should be false for SSL verification)
     * @param string $ip
     * @param string $verb
     * @param string $content_type For PUT
     *
     * @return string
     */

    /*
    public static function getAuthenticatedURL($bucket, $uri, $lifetime, $hostBucket = false, $https = false)
    {
        $expires = self::__getTime() + $lifetime;
        $uri = str_replace(array('%2F', '%2B'), array('/', '+'), rawurlencode($uri));
        return sprintf(($https ? 'https' : 'http').'://%s/%s?AWSAccessKeyId=%s&Expires=%u&Signature=%s',
        // $hostBucket ? $bucket : $bucket.'.s3.amazonaws.com', $uri, self::$__accessKey, $expires,
        $hostBucket ? $bucket : self::$endpoint.'/'.$bucket, $uri, self::$__accessKey, $expires,
        urlencode(self::__getHash("GET\n\n\n{$expires}\n/{$bucket}/{$uri}")));
    }
    */

    public static function getAuthenticatedURL($bucket, $uri, $lifetime, $hostBucket = false, $https = false, $ip = null, $verb = null, $content_type = null)
    {
        $expires = self::__getTime() + $lifetime;
        $uri = str_replace(['%2F'], ['/'], rawurlencode($uri));
        // $uri = str_replace(array('%2F', '%2B'), array('/', '+'), rawurlencode($uri));

        if (!$verb || !in_array($verb, ['GET', 'POST', 'PUT', 'HEAD', 'DELETE', 'OPTIONS'])) {
            $verb = 'GET';
        }

        if ($verb != 'POST' && $verb != 'PUT') {
            $content_type = null;
        }

        if (!$content_type) {
            $content_type = '';
        }

        if (!$bucket) {
            return sprintf(
                ($https ? 'https' : 'http').'://%s/?'.($ip ? 'ip='.$ip.'&' : '').'KID=%s&Expires=%u&ssig=%s',
                self::$endpoint,
                'sina,'.self::$__accessKey,
                $expires,
                urlencode(self::__getHash("{$verb}\n\n{$content_type}\n{$expires}\n/".($ip ? '?ip='.$ip : '')))
            );
        }

        return sprintf(
            ($https ? 'https' : 'http').'://%s/%s?'.($ip ? 'ip='.$ip.'&' : '').'KID=%s&Expires=%u&ssig=%s',
            $hostBucket ? $bucket : self::$endpoint.'/'.$bucket,
            $uri,
            'sina,'.self::$__accessKey,
            $expires,
            urlencode(self::__getHash("{$verb}\n\n{$content_type}\n{$expires}\n/{$bucket}/{$uri}".($ip ? '?ip='.$ip : '')))
        );
    }

    /**
     * Get upload POST parameters for form uploads.
     *
     * @param string   $bucket          Bucket name
     * @param string   $uriPrefix       Object URI prefix
     * @param constant $acl             ACL constant
     * @param int      $lifetime        Lifetime in seconds
     * @param int      $maxFileSize     Maximum filesize in bytes (default 5MB)
     * @param string   $successRedirect Redirect URL or 200 / 201 status code
     * @param array    $amzHeaders      Array of x-amz-meta-* headers
     * @param array    $headers         Array of request headers or content type as a string
     * @param bool     $flashVars       Includes additional "Filename" variable posted by Flash
     *
     * @return object
     */
    public static function getHttpUploadPostParams(
        $bucket,
        $uriPrefix = '',
        $acl = self::ACL_PRIVATE,
        $lifetime = 3600,
        $maxFileSize = 5242880,
        $successRedirect = '201',
        $amzHeaders = [],
        $headers = [],
        $flashVars = false
    )
    {
        // Create policy object
        $policy = new stdClass();
        $policy->expiration = gmdate('Y-m-d\TH:i:s.000\Z', self::__getTime() + $lifetime);
        $policy->conditions = [];
        $obj = new stdClass();
        $obj->bucket = $bucket;
        array_push($policy->conditions, $obj);
        $obj = new stdClass();
        $obj->acl = $acl;
        array_push($policy->conditions, $obj);

        $obj = new stdClass(); // 200 for non-redirect uploads
        if (is_numeric($successRedirect) && in_array((int) $successRedirect, [200, 201])) {
            $obj->success_action_status = (string) $successRedirect;
        } else { // URL
            $obj->success_action_redirect = $successRedirect;
        }
        // array_push($policy->conditions, $obj);

        if ($acl !== self::ACL_PUBLIC_READ) {
            array_push($policy->conditions, ['eq', '$acl', $acl]);
        }

        array_push($policy->conditions, ['starts-with', '$key', $uriPrefix]);
        if ($flashVars) {
            array_push($policy->conditions, ['starts-with', '$Filename', '']);
        }
        foreach (array_keys($headers) as $headerKey) {
            array_push($policy->conditions, ['starts-with', '$'.$headerKey, '']);
        }
        foreach ($amzHeaders as $headerKey => $headerVal) {
            $obj = new stdClass();
            $obj->{$headerKey} = (string) $headerVal;
            array_push($policy->conditions, $obj);
        }
        array_push($policy->conditions, ['content-length-range', 0, $maxFileSize]);
        $policy = base64_encode(str_replace('\/', '/', json_encode($policy)));

        // Create parameters
        $params = new stdClass();
        // $params->AWSAccessKeyId = 'SINA000000' . strtoupper(self::$__accessKey);
        $params->AWSAccessKeyId = self::$__accessKey;
        $params->key = $uriPrefix.'${filename}';
        $params->acl = $acl;
        $params->policy = $policy;
        unset($policy);
        $params->signature = self::__getHashRaw($params->policy);
        if (is_numeric($successRedirect) && in_array((int) $successRedirect, [200, 201])) {
            $params->success_action_status = (string) $successRedirect;
        } else {
            $params->success_action_redirect = $successRedirect;
        }
        foreach ($headers as $headerKey => $headerVal) {
            $params->{$headerKey} = (string) $headerVal;
        }
        foreach ($amzHeaders as $headerKey => $headerVal) {
            $params->{$headerKey} = (string) $headerVal;
        }

        $params->Policy = $params->policy;
        unset($params->policy);

        $params->Signature = $params->signature;
        unset($params->signature);

        return $params;
    }

    /**
     * Initiate Multipart Upload.
     *
     * @param string   $bucket         Destination bucket name
     * @param string   $uri            Destination object URI
     * @param constant $acl            ACL constant
     * @param array    $metaHeaders    Optional array of x-amz-meta-* headers
     * @param array    $requestHeaders Optional array of request headers (content type, disposition, etc.)
     * @param constant $storageClass   Storage class constant
     *
     * @return false|mixed
     */
    public static function initiateMultipartUpload($bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = [], $requestHeaders = [], $storageClass = self::STORAGE_CLASS_STANDARD)
    {
        $rest = new SCSRequest('POST', $bucket, $uri, self::$endpoint);
        $rest->setParameter('multipart', null);
        $rest->setHeader('Content-Length', 0);

        // Custom request headers (Content-Type, Content-Disposition, Content-Encoding)
        if (is_array($requestHeaders)) {
            foreach ($requestHeaders as $h => $v) {
                $rest->setHeader($h, $v);
            }
        } elseif (is_string($requestHeaders)) { // Support for legacy contentType parameter
            $rest->setHeader('Content-Type', $requestHeaders);
        }

        foreach ($metaHeaders as $h => $v) {
            $rest->setAmzHeader('x-amz-meta-'.$h, $v);
        }
        if ($storageClass !== self::STORAGE_CLASS_STANDARD) { // Storage class
            $rest->setAmzHeader('x-amz-storage-class', $storageClass);
        }
        $rest->setAmzHeader('x-amz-acl', $acl);

        // Content-Type
        if (!isset($requestHeaders['Content-Type'])) {
            $rest->setHeader('Content-Type', self::__getMIMEType($uri));
        }

        $rest = $rest->getResponse();

        // print_r($rest);

        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::initiateMultipartUpload({$bucket}, {$uri}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        // print_r($rest->body);

        return isset($rest->body->UploadId) ? [
            'bucket'    => $rest->body->Bucket,
            'key'       => $rest->body->Key,
            'upload_id' => $rest->body->UploadId,
        ] : false;
    }

    /**
     * List Parts.
     *
     * @param string $bucket   Bucket name
     * @param string $uri      Object URI
     * @param string $uploadId
     *
     * @return false|mixed
     */
    public static function listParts($bucket, $uri, $uploadId)
    {
        $rest = new SCSRequest('GET', $bucket, $uri, self::$endpoint);
        $rest->setParameter('uploadId', $uploadId);
        $response = $rest->getResponse();
        if ($response->error === false && $response->code !== 200) {
            $response->error = ['code' => $response->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($response->error !== false) {
            self::__triggerError(sprintf(
                "SCS::listParts({$bucket}, {$uri}, {$uploadId}): [%s] %s",
                $response->error['code'],
                $response->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        $results = [];

        if (isset($response->body, $response->body->Parts)) {
            foreach ($response->body->Parts as $c) {
                $a = get_object_vars($c);

                $results[intval($c->PartNumber)] = [
                    'part_number' => intval($c->PartNumber),
                    'time'        => strtotime((string) $a['Last-Modified']),
                    'size'        => (int) $c->Size,
                    'etag'        => (string) $c->ETag,
                ];
            }
        }

        return $results;
    }

    /**
     * Complete Multipart Upload.
     *
     * @param string $bucket   Bucket name
     * @param string $uri      Object URI
     * @param string $uploadId
     *
     * @return bool
     */
    public static function completeMultipartUpload($bucket, $uri, $uploadId, $parts)
    {
        $rest = new SCSRequest('POST', $bucket, $uri, self::$endpoint);
        $rest->setParameter('uploadId', $uploadId);

        if (true) { // 使用json格式
            $rest->setHeader('Content-Type', 'application/json');
            $rest->data = json_encode($parts);
        } else {
            $dom = new DOMDocument();
            $dom->formatOutput = true;
            $createCompleteMultipartUpload = $dom->createElement('CompleteMultipartUpload');
            $dom->appendChild($createCompleteMultipartUpload);

            foreach ($parts as $part) {
                $createPart = $dom->createElement('Part');
                $createCompleteMultipartUpload->appendChild($createPart);
                $createPart->appendChild($dom->createElement('PartNumber', $part['PartNumber']));
                $createPart->appendChild($dom->createElement('ETag', $part['ETag']));
            }

            $rest->removeParameter('formatter');
            $rest->setHeader('Content-Type', 'application/xml');
            $rest->data = $dom->saveXML();
        }

        $rest->size = strlen($rest->data);

        $rest = $rest->getResponse();

        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = ['code' => $rest->code, 'message' => 'Unexpected HTTP status'];
        }
        if ($rest->error !== false) {
            self::__triggerError(sprintf(
                "SCS::completeMultipartUpload({$bucket}, {$uri}, {$uploadId}, {$parts}): [%s] %s",
                $rest->error['code'],
                $rest->error['message']
            ), __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Get MIME type for file.
     *
     * To override the putObject() Content-Type, add it to $requestHeaders
     *
     * To use fileinfo, ensure the MAGIC environment variable is set
     *
     * @internal Used to get mime types
     *
     * @param string &$file File path
     *
     * @return string
     */
    private static function __getMIMEType(&$file)
    {
        static $exts = [
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'png'  => 'image/png', 'ico' => 'image/x-icon', 'pdf' => 'application/pdf',
            'tif'  => 'image/tiff', 'tiff' => 'image/tiff', 'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml', 'swf' => 'application/x-shockwave-flash',
            'zip'  => 'application/zip', 'gz' => 'application/x-gzip',
            'tar'  => 'application/x-tar', 'bz' => 'application/x-bzip',
            'bz2'  => 'application/x-bzip2', 'rar' => 'application/x-rar-compressed',
            'exe'  => 'application/x-msdownload', 'msi' => 'application/x-msdownload',
            'cab'  => 'application/vnd.ms-cab-compressed', 'txt' => 'text/plain',
            'asc'  => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html',
            'css'  => 'text/css', 'js' => 'text/javascript',
            'xml'  => 'text/xml', 'xsl' => 'application/xsl+xml',
            'ogg'  => 'application/ogg', 'mp3' => 'audio/mpeg', 'wav' => 'audio/x-wav',
            'avi'  => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg',
            'mov'  => 'video/quicktime', 'flv' => 'video/x-flv', 'php' => 'text/x-php',
        ];

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($exts[$ext])) {
            return $exts[$ext];
        }

        // Use fileinfo if available
        if (extension_loaded('fileinfo') && isset($_ENV['MAGIC'])
        && ($finfo = finfo_open(FILEINFO_MIME, $_ENV['MAGIC'])) !== false) {
            if (($type = finfo_file($finfo, $file)) !== false) {
                // Remove the charset and grab the last content-type
                $type = explode(' ', str_replace('; charset=', ';charset=', $type));
                $type = array_pop($type);
                $type = explode(';', $type);
                $type = trim(array_shift($type));
            }
            finfo_close($finfo);
            if ($type !== false && strlen($type) > 0) {
                return $type;
            }
        }

        return 'application/octet-stream';
    }

    /**
     * Get the current time.
     *
     * @internal Used to apply offsets to sytem time
     *
     * @return int
     */
    public static function __getTime()
    {
        return time() + self::$__timeOffset;
    }

    /**
     * Generate the auth string: "SINA AccessKey:Signature".
     *
     * @internal Used by SCSRequest::getResponse()
     *
     * @param string $string String to sign
     *
     * @return string
     */
    public static function __getSignature($string)
    {
        return 'SINA '.self::$__accessKey.':'.self::__getHash($string);
    }

    /**
     * Creates a HMAC-SHA1 hash.
     *
     * This uses the hash extension if loaded
     *
     * @internal Used by __getSignature()
     *
     * @param string $string String to sign
     *
     * @return string
     */
    private static function __getHash($string)
    {
        return substr(self::__getHashRaw($string), 5, 10);
        /*
        return base64_encode(extension_loaded('hash') ?
        hash_hmac('sha1', $string, self::$__secretKey, true) : pack('H*', sha1(
        (str_pad(self::$__secretKey, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) .
        pack('H*', sha1((str_pad(self::$__secretKey, 64, chr(0x00)) ^
        (str_repeat(chr(0x36), 64))) . $string)))));
        */
    }

    /**
     * Creates a HMAC-SHA1 hash.
     *
     * This uses the hash extension if loaded
     *
     * @internal Used by __getHash()
     *
     * @param string $string String to sign
     *
     * @return string
     */
    private static function __getHashRaw($string)
    {
        return base64_encode(hash_hmac('sha1', $string, self::$__secretKey, true));
    }
}

/**
 * SCS Request class.
 *
 * @see http://weibo.com/smcz
 *
 * @version 0.1.0-dev
 */
final class SCSRequest
{
    /**
     * SCS URI.
     *
     * @var string
     */
    private $endpoint;

    /**
     * Verb.
     *
     * @var string
     */
    private $verb;

    /**
     * SCS bucket name.
     *
     * @var string
     */
    private $bucket;

    /**
     * Object URI.
     *
     * @var string
     */
    private $uri;

    /**
     * Final object URI.
     *
     * @var string
     */
    private $resource = '';

    /**
     * Additional request parameters.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * Amazon specific request headers.
     *
     * @var array
     */
    private $amzHeaders = [];

    /**
     * HTTP request headers.
     *
     * @var array
     */
    private $headers = [
        'Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => '',
    ];

    /**
     * Use HTTP PUT?
     *
     * @var bool
     */
    public $fp = false;

    /**
     * PUT file size.
     *
     * @var int
     */
    public $size = 0;

    /**
     * PUT post fields.
     *
     * @var array
     */
    public $data = false;

    /**
     * SCS request respone.
     *
     * @var object
     */
    public $response;

    /**
     * Constructor.
     *
     * @param string $verb     Verb
     * @param string $bucket   Bucket name
     * @param string $uri      Object URI
     * @param string $endpoint SCS endpoint URI
     */
    public function __construct($verb, $bucket = '', $uri = '', $endpoint = 'sinacloud.net')
    {
        $this->endpoint = $endpoint;
        $this->verb = $verb;
        $this->bucket = $bucket;
        $this->uri = $uri !== '' ? '/'.str_replace('%2F', '/', rawurlencode($uri)) : '/';

        // if ($this->bucket !== '')
        //	$this->resource = '/'.$this->bucket.$this->uri;
        // else
        //	$this->resource = $this->uri;

        if ($this->bucket !== '') {
            if ($this->__dnsBucketName($this->bucket)) {
                $this->headers['Host'] = $this->bucket.'.'.$this->endpoint;
                $this->resource = '/'.$this->bucket.$this->uri;
            } else {
                $this->headers['Host'] = $this->endpoint;
                $this->uri = $this->uri;
                if ($this->bucket !== '') {
                    $this->uri = '/'.$this->bucket.$this->uri;
                }
                $this->bucket = '';
                $this->resource = $this->uri;
            }
        } else {
            $this->headers['Host'] = $this->endpoint;
            $this->resource = $this->uri;
        }

        // $this->headers['Date'] = gmdate('D, d M Y H:i:s T');
        $this->headers['Date'] = gmdate('D, d M Y H:i:s T', SCS::__getTime());
        $this->response = new STDClass();
        $this->response->error = false;
        $this->response->body = null;
        $this->response->headers = [];
        $this->setParameter('formatter', 'json');
    }

    /**
     * Set request parameter.
     *
     * @param string $key   Key
     * @param string $value Value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Remove request parameter.
     *
     * @param string $key Key
     */
    public function removeParameter($key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * Set request header.
     *
     * @param string $key   Key
     * @param string $value Value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Set x-amz-meta-* header.
     *
     * @param string $key   Key
     * @param string $value Value
     */
    public function setAmzHeader($key, $value)
    {
        $this->amzHeaders[$key] = $value;
    }

    /**
     * Get the SCS response.
     *
     * @return false|object
     */
    public function getResponse()
    {
        $query = '';
        if (sizeof($this->parameters) > 0) {
            $query = substr($this->uri, -1) !== '?' ? '?' : '&';
            foreach ($this->parameters as $var => $value) {
                if ($value == null || $value == '') {
                    $query .= $var.'&';
                } else {
                    $query .= $var.'='.rawurlencode($value).'&';
                }
            }

            $query = substr($query, 0, -1);
            $this->uri .= $query;

            /*
            if (array_key_exists('acl', $this->parameters) ||
                array_key_exists('location', $this->parameters) ||
                array_key_exists('torrent', $this->parameters) ||
                array_key_exists('website', $this->parameters) ||
                array_key_exists('logging', $this->parameters))
            {
                $this->resource .= $query;
            }
            */

            $single_filter_list = ['acl', 'location', 'torrent', 'website', 'logging', 'relax', 'meta', 'uploads', 'part', 'copy', 'multipart'];
            $double_filter_list = ['uploadId', 'ip', 'partNumber'];

            foreach ($this->parameters as $var => $value) {
                if (in_array($var, $single_filter_list)) {
                    $this->resource .= '?'.$var;

                    break;
                }
            }

            $query_for_sign_list = [];

            foreach ($this->parameters as $var => $value) {
                if (in_array($var, $double_filter_list)) {
                    $query_for_sign_list[$var] = $value;
                }
            }

            if (count($query_for_sign_list) > 0) {
                ksort($query_for_sign_list);

                $query_for_sign = '';

                foreach ($query_for_sign_list as $key => $value) {
                    $query_for_sign .= $key.'='.rawurlencode($value).'&';
                }

                $query_for_sign = substr($query_for_sign, 0, -1);

                if ($query_for_sign) {
                    $this->resource .= (strpos($this->resource, '?') === false ? '?' : '&').$query_for_sign;
                }
            }
        }

        $url = (SCS::$useSSL ? 'https://' : 'http://').($this->headers['Host'] !== '' ? $this->headers['Host'] : $this->endpoint).$this->uri;

        /* @TODO delete */

        // $url = (SCS::$useSSL ? 'https://' : 'http://') . '58.63.236.206' . $this->uri;

        // var_dump('bucket: ' . $this->bucket, 'uri: ' . $this->uri, 'resource: ' . $this->resource, 'url: ' . $url);

        // Basic setup
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'SCS/console');

        if (SCS::$useSSL) {
            // SSL Validation can now be optional for those with broken OpenSSL installations
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SCS::$useSSLValidation ? 2 : 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SCS::$useSSLValidation ? 1 : 0);

            if (SCS::$sslKey !== null) {
                curl_setopt($curl, CURLOPT_SSLKEY, SCS::$sslKey);
            }
            if (SCS::$sslCert !== null) {
                curl_setopt($curl, CURLOPT_SSLCERT, SCS::$sslCert);
            }
            if (SCS::$sslCACert !== null) {
                curl_setopt($curl, CURLOPT_CAINFO, SCS::$sslCACert);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        if (SCS::$proxy != null && isset(SCS::$proxy['host'])) {
            curl_setopt($curl, CURLOPT_PROXY, SCS::$proxy['host']);
            curl_setopt($curl, CURLOPT_PROXYTYPE, SCS::$proxy['type']);
            if (isset(SCS::$proxy['user'], SCS::$proxy['pass']) && SCS::$proxy['user'] != null && SCS::$proxy['pass'] != null) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', SCS::$proxy['user'], SCS::$proxy['pass']));
            }
        }

        // Headers
        $headers = [];
        $amz = [];
        foreach ($this->amzHeaders as $header => $value) {
            if (strlen($value) > 0) {
                $headers[] = $header.': '.$value;
            }
        }
        foreach ($this->headers as $header => $value) {
            if (strlen($value) > 0) {
                $headers[] = $header.': '.$value;
            }
        }

        // Collect AMZ headers for signature
        foreach ($this->amzHeaders as $header => $value) {
            if (strlen($value) > 0) {
                $amz[] = strtolower($header).':'.$value;
            }
        }

        // AMZ headers must be sorted
        if (sizeof($amz) > 0) {
            // sort($amz);
            usort($amz, [&$this, '__sortMetaHeadersCmp']);
            $amz = "\n".implode("\n", $amz);
        } else {
            $amz = '';
        }

        if (SCS::hasAuth()) {
            // Authorization string (CloudFront stringToSign should only contain a date)
            if ($this->headers['Host'] == 'cloudfront.amazonaws.com') {
                $headers[] = 'Authorization: '.SCS::__getSignature($this->headers['Date']);
            } else {
                if (isset($this->headers['s-sina-sha1'])) {
                    $this->headers['Content-MD5'] = $this->headers['s-sina-sha1'];
                }

                $headers[] = 'Authorization: '.SCS::__getSignature(
                    $this->verb."\n".
                    $this->headers['Content-MD5']."\n".
                    $this->headers['Content-Type']."\n".
                    $this->headers['Date'].$amz."\n".
                    $this->resource
                );
            }
        }

        /* @todo delete */
        // $headers[] = 'Host: ' . ($this->headers['Host'] !== '' ? $this->headers['Host'] : $this->endpoint);
        // print_r($headers);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, [&$this, '__responseWriteCallback']);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [&$this, '__responseHeaderCallback']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        /* 必要时设置超时时间
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1200);
        */

        // Request types
        switch ($this->verb) {
            case 'GET': break;

            case 'PUT': case 'POST': // POST only used for CloudFront
                if ($this->fp !== false) {
                    curl_setopt($curl, CURLOPT_PUT, true);
                    curl_setopt($curl, CURLOPT_INFILE, $this->fp);
                    if ($this->size >= 0) {
                        curl_setopt($curl, CURLOPT_INFILESIZE, $this->size);
                    }
                } elseif ($this->data !== false) {
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
                } else {
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
                }

                break;

            case 'HEAD':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
                curl_setopt($curl, CURLOPT_NOBODY, true);

                break;

            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

                break;

            default: break;
        }

        // Execute, grab errors
        if (curl_exec($curl)) {
            $this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        } else {
            $this->response->error = [
                'code'     => curl_errno($curl),
                'message'  => curl_error($curl),
                'resource' => $this->resource,
            ];
        }

        // echo $this->response->body;
        @curl_close($curl);

        // Parse body into XML | JSON
        if ($this->response->error === false && isset($this->response->headers['type'])
        && ($this->response->headers['type'] == 'application/xml' || $this->response->headers['type'] == 'application/json') && isset($this->response->body)) {
            if ($this->response->headers['type'] == 'application/json') {
                $this->response->body = json_decode($this->response->body);
            } else {
                $this->response->body = simplexml_load_string($this->response->body);
            }

            // Grab SCS errors
            if (!in_array($this->response->code, [200, 204, 206])
            && isset($this->response->body->Code, $this->response->body->Message)) {
                $this->response->error = [
                    'code'    => (string) $this->response->body->Code,
                    'message' => (string) $this->response->body->Message,
                ];
                if (isset($this->response->body->Resource)) {
                    $this->response->error['resource'] = (string) $this->response->body->Resource;
                }
                unset($this->response->body);
            }
        }

        // Clean up file resources
        if ($this->fp !== false && is_resource($this->fp)) {
            fclose($this->fp);
        }

        return $this->response;
    }

    /**
     * Sort compare for meta headers.
     *
     * @internal Used to sort x-amz meta headers
     *
     * @param string $a String A
     * @param string $b String B
     *
     * @return int
     */
    private function __sortMetaHeadersCmp($a, $b)
    {
        $lenA = strpos($a, ':');
        $lenB = strpos($b, ':');
        $minLen = min($lenA, $lenB);
        $ncmp = strncmp($a, $b, $minLen);
        if ($lenA == $lenB) {
            return $ncmp;
        }
        if ($ncmp == 0) {
            return $lenA < $lenB ? -1 : 1;
        }

        return $ncmp;
    }

    /**
     * CURL write callback.
     *
     * @param resource &$curl CURL resource
     * @param string   &$data Data
     *
     * @return int
     */
    private function __responseWriteCallback($curl, $data)
    {
        if (in_array($this->response->code, [200, 206]) && $this->fp !== false) {
            return fwrite($this->fp, $data);
        }

        $this->response->body .= $data;

        return strlen($data);
    }

    /**
     * Check DNS conformity.
     *
     * @param string $bucket Bucket name
     *
     * @return bool
     */
    private function __dnsBucketName($bucket)
    {
        if (strlen($bucket) > 63 || preg_match('/[^a-z0-9\\.-]/', $bucket) > 0) {
            return false;
        }
        if (strstr($bucket, '-.') !== false) {
            return false;
        }
        if (strstr($bucket, '..') !== false) {
            return false;
        }
        if (!preg_match('/^[0-9a-z]/', $bucket)) {
            return false;
        }
        if (!preg_match('/[0-9a-z]$/', $bucket)) {
            return false;
        }

        return true;
    }

    /**
     * CURL header callback.
     *
     * @param resource &$curl CURL resource
     * @param string   &$data Data
     *
     * @return int
     */
    private function __responseHeaderCallback($curl, $data)
    {
        if (($strlen = strlen($data)) <= 2) {
            return $strlen;
        }
        if (substr($data, 0, 4) == 'HTTP') {
            $this->response->code = (int) substr($data, 9, 3);
        } else {
            $data = trim($data);
            if (strpos($data, ': ') === false) {
                return $strlen;
            }
            [$header, $value] = explode(': ', $data, 2);
            if ($header == 'Last-Modified') {
                $this->response->headers['time'] = strtotime($value);
            } elseif ($header == 'Date') {
                $this->response->headers['date'] = strtotime($value);
            } elseif ($header == 'Content-Length') {
                $this->response->headers['size'] = (int) $value;
            } elseif ($header == 'Content-Type') {
                $this->response->headers['type'] = $value;
            } elseif ($header == 'ETag') {
                $this->response->headers['hash'] = $value[0] == '"' ? substr($value, 1, -1) : $value;
            } elseif (preg_match('/^x-amz-meta-.*$/', $header)) {
                $this->response->headers[$header] = $value;
            }
        }

        return $strlen;
    }
}

/**
 * SCS exception class.
 *
 * @see http://weibo.com/smcz
 *
 * @version 0.1.0-dev
 */
class SCSException extends Exception
{
    /**
     * Class constructor.
     *
     * @param string $message Exception message
     * @param string $file    File in which exception was created
     * @param string $line    Line number on which exception was created
     * @param int    $code    Exception code
     */
    public function __construct($message, $file, $line, $code = 0)
    {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
}
