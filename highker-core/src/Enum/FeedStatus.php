<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class FeedStatus extends BaseEnum
{
    public const PENDING = 0;
    public const APPROVE = 1;
    public const FORBIDDEN = 2;

    public const LIST = [
        self::PENDING,
        self::APPROVE,
        self::FORBIDDEN,
    ];

    public const MAP = [
        self::PENDING   => '待审核',
        self::APPROVE   => '通过',
        self::FORBIDDEN => '屏蔽',
    ];
}
