<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class UserAvatarSize extends BaseEnum
{
    public const SMALL = 'small';
    public const MEDIUM = 'medium';
    public const BIG = 'big';

    public const LIST = [
        self::SMALL,
        self::MEDIUM,
        self::BIG,
    ];

    public const MAP = [
        self::SMALL  => '!avatar-small',
        self::MEDIUM => '!avatar-small',
        self::BIG    => '!avatar-small',
    ];
}
