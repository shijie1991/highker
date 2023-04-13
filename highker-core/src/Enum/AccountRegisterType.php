<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Class AccountRegisterType.
 */
class AccountRegisterType extends BaseEnum
{
    public const PHONE = 1;
    public const WECHAT = 2;
    public const MINI_PROGRAM = 3;
    public const FAKER = 4;

    public const LIST = [
        self::PHONE,
        self::WECHAT,
        self::MINI_PROGRAM,
        self::FAKER,
    ];

    public const MAP = [
        self::PHONE        => '手机',
        self::WECHAT       => '微信',
        self::MINI_PROGRAM => '微信小程序',
        self::FAKER        => '虚拟用户',
    ];

    public const ALL_3RD = [
        self::WECHAT,
    ];

    /**
     * 判断给定的类型是否是第三方登录.
     *
     * @param $type
     */
    public static function is3rd($type): bool
    {
        return in_array($type, self::ALL_3RD);
    }
}
