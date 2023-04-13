<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Class ClientType.
 */
class ClientType extends BaseEnum
{
    public const PC = 1;
    public const ANDROID = 2;
    public const IOS = 3;
    public const H5 = 4;
    public const WECHAT = 5;

    public const LIST = [
        self::PC,
        self::ANDROID,
        self::IOS,
        self::H5,
        self::WECHAT,
    ];

    public const MAP = [
        self::PC      => 'PC',
        self::ANDROID => 'Android',
        self::IOS     => 'IOS',
        self::H5      => 'H5',
        self::WECHAT  => 'Wechat',
    ];
}
