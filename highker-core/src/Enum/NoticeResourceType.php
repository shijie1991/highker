<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * 站内通知 触发结果的类型.
 *
 * Class NoticeResourceType
 */
class NoticeResourceType extends BaseEnum
{
    public const COMMENT = 1;
    public const COMMENT_REPLY = 2;

    public const LIST = [
        self::COMMENT,
        self::COMMENT_REPLY,
    ];

    public const MAP = [
        self::COMMENT       => '评论',
        self::COMMENT_REPLY => '回复',
    ];
}
