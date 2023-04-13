<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class UserPurpose extends BaseEnum
{
    public const FRIENDS = 1;
    public const SOUL = 2;
    public const LIFE = 3;
    public const OTHER = 4;

    public const LIST = [
        self::FRIENDS,
        self::SOUL,
        self::LIFE,
        self::OTHER,
    ];

    public const MAP = [
        self::FRIENDS => '认识朋友',
        self::SOUL    => '灵魂社交',
        self::LIFE    => '记录生活',
        self::OTHER   => '其他',
    ];
}
