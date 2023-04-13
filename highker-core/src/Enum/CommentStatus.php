<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class CommentStatus extends BaseEnum
{
    public const APPROVE = 0;
    public const FORBIDDEN = 1;

    public const LIST = [
        self::APPROVE,
        self::FORBIDDEN,
    ];

    public const MAP = [
        self::APPROVE   => '通过',
        self::FORBIDDEN => '屏蔽',
    ];
}
