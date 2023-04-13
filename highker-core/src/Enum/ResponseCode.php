<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class ResponseCode extends BaseEnum
{
    public const OK = 200200;

    public const SYSTEM_ERROR = 500500;

    public const UNAUTHORIZED = 400401;

    public const MESSAGE_WAITING_REPLY = 500001;
    public const BOX_NOT_FOND = 500002;

    public const LIST = [
        self::OK,
        self::SYSTEM_ERROR,
        self::UNAUTHORIZED,
        self::BOX_NOT_FOND,
    ];

    public const MAP = [
        self::OK                    => 'OK',
        self::MESSAGE_WAITING_REPLY => '对方未回复 只能发送三条招呼',
        self::SYSTEM_ERROR          => '系统错误',
        self::UNAUTHORIZED          => '用户未授权',
        self::BOX_NOT_FOND          => '未搜索到盲盒,发送一个试试',
    ];
}
