<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Utils;

use Exception;
use finfo;

class ImageUtils
{
    public const TYPE_GIF = 'gif';  // 1
    public const TYPE_JPEG = 'jpg';  // 2
    public const TYPE_PNG = 'png';  // 3
    public const TYPE_SWF = 'swf';  // 4
    public const TYPE_PSD = 'psd';  // 5
    public const TYPE_BMP = 'bmp';  // 6
    public const TYPE_TIFF_II = 'tiff'; // 7//（Intel 字节顺序）
    public const TYPE_TIFF_MM = 'tiff'; // 8//（Motorola 字节顺序）
    public const TYPE_JPC = 'jpc';  // 9
    public const TYPE_JP2 = 'jp2';  // 10
    public const TYPE_JPX = 'jpx';  // 11
    public const TYPE_JB2 = 'jb2';  // 12
    public const TYPE_SWC = 'swc';  // 13
    public const TYPE_IFF = 'iff';  // 14
    public const TYPE_WBMP = 'wbmp'; // 15
    public const TYPE_XBM = 'xbm';  // 16
    public const TYPE_ICO = 'ico';  // 17
    public const TYPE_WEBP = 'webp'; // 18

    public const EXIF_TYPE = [
        1  => self::TYPE_GIF,
        2  => self::TYPE_JPEG,
        3  => self::TYPE_PNG,
        4  => self::TYPE_SWF,
        5  => self::TYPE_PSD,
        6  => self::TYPE_BMP,
        7  => self::TYPE_TIFF_II,
        8  => self::TYPE_TIFF_MM,
        9  => self::TYPE_JPC,
        10 => self::TYPE_JP2,
        11 => self::TYPE_JPX,
        12 => self::TYPE_JB2,
        13 => self::TYPE_SWC,
        14 => self::TYPE_IFF,
        15 => self::TYPE_WBMP,
        16 => self::TYPE_XBM,
    ];

    public const MIME_TYPE = [
        'image/gif'                     => self::TYPE_GIF,
        'image/jpeg'                    => self::TYPE_JPEG,
        'image/pjpeg'                   => self::TYPE_JPEG,
        'image/png'                     => self::TYPE_PNG,
        'application/x-shockwave-flash' => self::TYPE_SWF,
        'image/x-photoshop'             => self::TYPE_PSD,
        'image/bmp'                     => self::TYPE_BMP,
        'image/tiff'                    => self::TYPE_TIFF_II,
        'image/jp2'                     => self::TYPE_JP2,
        'image/jpx'                     => self::TYPE_JPX,
        'image/x-xbitmap'               => self::TYPE_XBM,
        'image/vnd.wap.wbmp'            => self::TYPE_WBMP,
        'image/x-icon'                  => self::TYPE_ICO,
        'image/webp'                    => self::TYPE_WEBP,
    ];

    /**
     * 返回图片长宽大小.
     *
     * @return array|false
     *
     * @throws Exception
     */
    public static function getSize(string $file)
    {
        if (!function_exists('getimagesize')) {
            throw new Exception('Missing Function getimagesize (GD)');
        }

        return getimagesize($file);
    }

    /**
     * @param $bin mixed
     *
     * @return string
     *
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public static function getExtensionFromBinary(mixed $bin)
    {
        $fin = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fin->buffer($bin);

        return self::MIME_TYPE[$mime];
    }
}
