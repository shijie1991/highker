<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support\FileDriver;

use finfo;
use HighKer\Core\Support\FileDriver\scs\class\SCS;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;

class ScsAdapter implements FilesystemAdapter
{
    // protected ?Auth $authManager = null;
    // protected ?UploadManager $uploadManager = null;
    // protected ?BucketManager $bucketManager = null;
    // protected ?CdnManager $cdnManager = null;

    public function __construct(
        protected string $accessKey,
        protected string $secretKey,
        protected string $bucket,
        protected string $domain
    ) {
        SCS::setAuth($accessKey, $secretKey);
        SCS::setExceptions(true);
    }

    public function fileExists(string $path): bool
    {
        return !empty(SCS::getBucket($this->bucket, $path, null, 1));
    }

    public function directoryExists(string $path): bool
    {
        return $this->fileExists($path);
    }

    /** @noinspection PhpComposerExtensionStubsInspection */
    public function write(string $path, string $contents, Config $config): void
    {
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($contents);

        SCS::putObjectString($contents, $this->bucket, $path, '', [], $mime_type);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $data = '';

        while (!feof($contents)) {
            $data .= fread($contents, 1024);
        }

        /** @noinspection PhpComposerExtensionStubsInspection */
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($data);

        SCS::putObjectString($data, $this->bucket, $path, '', [], $mime_type);
    }

    public function read(string $path): string
    {
        $result = file_get_contents($this->getUrl($path));
        if ($result === false) {
            throw UnableToReadFile::fromLocation($path);
        }

        return $result;
    }

    public function readStream(string $path)
    {
        if (ini_get('allow_url_fopen')) {
            if ($result = fopen($this->getUrl($path), 'r')) {
                return $result;
            }
        }

        throw UnableToReadFile::fromLocation($path);
    }

    public function delete(string $path): void
    {
        if ($this->fileExists($path)) {
            SCS::deleteObject($this->bucket, $path);
        } else {
            throw UnableToDeleteFile::atLocation($path);
        }
    }

    public function deleteDirectory(string $path): void
    {
    }

    public function createDirectory(string $path, Config $config): void
    {
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path);
    }

    public function visibility(string $path): FileAttributes
    {
        throw UnableToRetrieveMetadata::visibility($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if ($meta->mimeType() === null) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return $meta;
    }

    public function lastModified(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if ($meta->lastModified() === null) {
            throw UnableToRetrieveMetadata::lastModified($path);
        }

        return $meta;
    }

    public function fileSize(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if ($meta->fileSize() === null) {
            throw UnableToRetrieveMetadata::fileSize($path);
        }

        return $meta;
    }

    public function listContents(string $path, bool $deep): iterable
    {
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->copy($source, $destination, $config);
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            SCS::copyObject($this->bucket, $source, $this->bucket, $destination);
        } catch (\Throwable $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }
    }

    public function getUrl(string $path): string
    {
        $segments = $this->parseUrl($path);
        $query = empty($segments['query']) ? '' : '?'.$segments['query'];

        return $this->normalizeHost($this->domain).ltrim(implode('/', array_map('rawurlencode', explode('/', $segments['path']))), '/').$query;
    }

    protected function normalizeHost($domain): string
    {
        if (stripos($domain, 'https://') !== 0 && stripos($domain, 'http://') !== 0) {
            $domain = "http://{$domain}";
        }

        return rtrim($domain, '/').'/';
    }

    protected static function parseUrl($url): array
    {
        $result = [];

        // Build arrays of values we need to decode before parsing
        $entities = [
            '%21',
            '%2A',
            '%27',
            '%28',
            '%29',
            '%3B',
            '%3A',
            '%40',
            '%26',
            '%3D',
            '%24',
            '%2C',
            '%2F',
            '%3F',
            '%23',
            '%5B',
            '%5D',
            '%5C',
        ];
        $replacements = ['!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '$', ',', '/', '?', '#', '[', ']', '/'];

        // Create encoded URL with special URL characters decoded so it can be parsed
        // All other characters will be encoded
        $encodedURL = str_replace($entities, $replacements, urlencode($url));

        // Parse the encoded URL
        $encodedParts = parse_url($encodedURL);

        // Now, decode each value of the resulting array
        if ($encodedParts) {
            foreach ($encodedParts as $key => $value) {
                $result[$key] = urldecode(str_replace($replacements, $entities, $value));
            }
        }

        return $result;
    }

    protected function getMetadata($path): FileAttributes|array
    {
        try {
            $mata = get_object_vars(SCS::getMeta($this->bucket, $path));
        } catch (\Throwable $e) {
            $mata = [];
        }

        return $this->normalizeFileInfo($mata);
    }

    protected function normalizeFileInfo(array $stats): FileAttributes
    {
        return new FileAttributes(
            $stats['File-Name'],
            $stats['Size'] ?? null,
            null,
            isset($stats['Last-Modified']) ? strtotime($stats['Last-Modified']) : null,
            $stats['Type'] ?? null
        );
    }
}
