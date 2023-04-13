<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

final class SCSWrapper extends SCS
{
    private $position = 0;

    private $mode = '';

    private $buffer;

    public function url_stat($path, $flags)
    {
        self::__getURL($path);

        return (($info = self::getObjectInfo($this->url['host'], $this->url['path'])) !== false) ?
        ['size' => $info['size'], 'mtime' => $info['time'], 'ctime' => $info['time']] : false;
    }

    public function unlink($path)
    {
        self::__getURL($path);

        return self::deleteObject($this->url['host'], $this->url['path']);
    }

    public function mkdir($path, $mode, $options)
    {
        self::__getURL($path);

        return self::putBucket($this->url['host'], self::__translateMode($mode));
    }

    public function rmdir($path)
    {
        self::__getURL($path);

        return self::deleteBucket($this->url['host']);
    }

    public function dir_opendir($path, $options)
    {
        self::__getURL($path);
        if (($contents = self::getBucket($this->url['host'], $this->url['path'])) !== false) {
            $pathlen = strlen($this->url['path']);
            if (substr($this->url['path'], -1) == '/') {
                ++$pathlen;
            }
            $this->buffer = [];
            foreach ($contents as $file) {
                if ($pathlen > 0) {
                    $file['name'] = substr($file['name'], $pathlen);
                }
                $this->buffer[] = $file;
            }

            return true;
        }

        return false;
    }

    public function dir_readdir()
    {
        return (isset($this->buffer[$this->position])) ? $this->buffer[$this->position++]['name'] : false;
    }

    public function dir_rewinddir()
    {
        $this->position = 0;
    }

    public function dir_closedir()
    {
        $this->position = 0;
        unset($this->buffer);
    }

    public function stream_close()
    {
        if ($this->mode == 'w') {
            self::putObject($this->buffer, $this->url['host'], $this->url['path']);
        }
        $this->position = 0;
        unset($this->buffer);
    }

    public function stream_stat()
    {
        if (is_object($this->buffer) && isset($this->buffer->headers)) {
            return [
                'size'  => $this->buffer->headers['size'],
                'mtime' => $this->buffer->headers['time'],
                'ctime' => $this->buffer->headers['time'],
            ];
        }
        if (($info = self::getObjectInfo($this->url['host'], $this->url['path'])) !== false) {
            return ['size' => $info['size'], 'mtime' => $info['time'], 'ctime' => $info['time']];
        }

        return false;
    }

    public function stream_flush()
    {
        $this->position = 0;

        return true;
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        if (!in_array($mode, ['r', 'rb', 'w', 'wb'])) {
            return false;
        } // Mode not supported
        $this->mode = substr($mode, 0, 1);
        self::__getURL($path);
        $this->position = 0;
        if ($this->mode == 'r') {
            if (($this->buffer = self::getObject($this->url['host'], $this->url['path'])) !== false) {
                if (is_object($this->buffer->body)) {
                    $this->buffer->body = (string) $this->buffer->body;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public function stream_read($count)
    {
        if ($this->mode !== 'r' && $this->buffer !== false) {
            return false;
        }
        $data = substr(is_object($this->buffer) ? $this->buffer->body : $this->buffer, $this->position, $count);
        $this->position += strlen($data);

        return $data;
    }

    public function stream_write($data)
    {
        if ($this->mode !== 'w') {
            return 0;
        }
        $left = substr($this->buffer, 0, $this->position);
        $right = substr($this->buffer, $this->position + strlen($data));
        $this->buffer = $left.$data.$right;
        $this->position += strlen($data);

        return strlen($data);
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_eof()
    {
        return $this->position >= strlen(is_object($this->buffer) ? $this->buffer->body : $this->buffer);
    }

    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->buffer->body) && $offset >= 0) {
                    $this->position = $offset;

                    return true;
                }

return false;

                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;

                    return true;
                }

return false;

                break;

            case SEEK_END:
                $bytes = strlen($this->buffer->body);
                if ($bytes + $offset >= 0) {
                    $this->position = $bytes + $offset;

                    return true;
                }

return false;

                break;

            default: return false;
        }
    }

    private function __getURL($path)
    {
        $this->url = parse_url($path);
        if (!isset($this->url['scheme']) || $this->url['scheme'] !== 'scs') {
            return $this->url;
        }
        if (isset($this->url['user'], $this->url['pass'])) {
            self::setAuth($this->url['user'], $this->url['pass']);
        }
        $this->url['path'] = isset($this->url['path']) ? substr($this->url['path'], 1) : '';
    }

    private function __translateMode($mode)
    {
        $acl = self::ACL_PRIVATE;
        if (($mode & 0x0020) || ($mode & 0x0004)) {
            $acl = self::ACL_PUBLIC_READ;
        }
        // You probably don't want to enable public write access
        if (($mode & 0x0010) || ($mode & 0x0008) || ($mode & 0x0002) || ($mode & 0x0001)) {
            $acl = self::ACL_PUBLIC_READ;
        } // $acl = self::ACL_PUBLIC_READ_WRITE;

        return $acl;
    }
} stream_wrapper_register('scs', 'SCSWrapper');
