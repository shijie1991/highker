<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * 站内通知 触发者的类型.
 *
 * Class NoticeTriggerType
 */
class NoticeTriggerType extends BaseEnum
{
    public const USER = 1;
    public const ADMIN = 2;
    public const SYSTEM = 3;

    public const LIST = [
        self::USER,
        self::ADMIN,
        self::SYSTEM,
    ];

    public const MAP = [
        self::USER   => '用户触发',
        self::ADMIN  => '管理员触发',
        self::SYSTEM => '系统触发',
    ];
}
