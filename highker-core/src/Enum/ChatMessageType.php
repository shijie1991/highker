<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Class ChatMessageType.
 */
class ChatMessageType extends BaseEnum
{
    public const TEXT = 1;
    public const IMAGE = 2;
    public const VOICE = 3;

    public const LIST = [
        self::TEXT,
        self::IMAGE,
        self::VOICE,
    ];

    public const MAP = [
        self::TEXT  => '文字',
        self::IMAGE => '图片',
        self::VOICE => '语音',
    ];
}
