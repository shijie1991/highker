<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class CommentLevel extends BaseEnum
{
    public const COMMENT = 0;
    public const REPLY = 1;
    public const SECOND_REPLY = 2;

    public const LIST = [
        self::COMMENT,
        self::REPLY,
        self::SECOND_REPLY,
    ];

    public const MAP = [
        self::COMMENT      => '评论',
        self::REPLY        => '一级回复',
        self::SECOND_REPLY => '二级回复',
    ];
}
