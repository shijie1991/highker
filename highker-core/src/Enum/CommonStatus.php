<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class CommonStatus extends BaseEnum
{
    public const ENABLED = 1;
    public const DISABLE = 0;

    public const LIST = [
        self::ENABLED,
        self::DISABLE,
    ];

    public const MAP = [
        self::ENABLED => '开启',
        self::DISABLE => '关闭',
    ];
}
