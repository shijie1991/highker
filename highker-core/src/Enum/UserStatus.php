<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class UserStatus extends BaseEnum
{
    public const NORMAL = 0;
    public const LOCKED = 1;
    public const FORBIDDEN = 2;
    public const NEED_INIT = 3;

    public const LIST = [
        self::NORMAL,
        self::LOCKED,
        self::FORBIDDEN,
        self::NEED_INIT,
    ];

    public const MAP = [
        self::NORMAL    => '正常',
        self::LOCKED    => '锁定',
        self::FORBIDDEN => '禁用',
        self::NEED_INIT => '需要初始化',
    ];
}
