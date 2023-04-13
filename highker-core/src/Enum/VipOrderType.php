<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class VipOrderType extends BaseEnum
{
    public const USER = 1;
    public const SYSTEM = 2;

    public const LIST = [
        self::USER,
        self::SYSTEM,
    ];

    public const MAP = [
        self::USER   => '用户下单',
        self::SYSTEM => '系统赠送',
    ];
}
