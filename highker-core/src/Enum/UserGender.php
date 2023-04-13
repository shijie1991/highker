<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class UserGender extends BaseEnum
{
    public const UNKNOWN = 0;
    public const MALE = 1;
    public const FEMALE = 2;

    public const LIST = [
        self::UNKNOWN,
        self::MALE,
        self::FEMALE,
    ];

    public const MAP = [
        self::UNKNOWN => '未知',
        self::MALE    => '男',
        self::FEMALE  => '女',
    ];
}
