<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * 站内通知 触发主体的类型.
 *
 * Class NoticeTargetType
 */
class NoticeTargetType extends BaseEnum
{
    public const FEED = 1;
    public const COMMENT = 2;
    public const COMMENT_REPLY = 3;
    public const USER = 4;

    public const LIST = [
        self::FEED,
        self::COMMENT,
        self::COMMENT_REPLY,
        self::USER,
    ];

    public const MAP = [
        self::FEED          => '动态',
        self::COMMENT       => '评论',
        self::COMMENT_REPLY => '回复',
        self::USER          => '用户',
    ];
}
