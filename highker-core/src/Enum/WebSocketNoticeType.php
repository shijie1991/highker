<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Websocket 通知类型.
 *
 * Class WebSocketNoticeType
 */
class WebSocketNoticeType extends BaseEnum
{
    public const CHAT = 1;
    public const SYSTEM = 2;

    public const LIST = [
        self::CHAT,
        self::SYSTEM,
    ];

    public const MAP = [
        self::CHAT   => '聊天通知',
        self::SYSTEM => '系统通知',
    ];
}
