<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * 站内通知 事件类型.
 *
 * Class NoticeEvent
 */
class NoticeType extends BaseEnum
{
    public const INTERACTIVE = 1;
    public const SYSTEM = 2;

    public const LIST = [
        self::INTERACTIVE,
        self::SYSTEM,
    ];

    public const MAP = [
        self::INTERACTIVE => '互动通知',
        self::SYSTEM      => '系统通知',
    ];
}
