<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class UserReviewType extends BaseEnum
{
    public const NAME = 0;
    public const AVATAR = 1;
    public const DESCRIPTION = 2;

    public const LIST = [
        self::NAME,
        self::AVATAR,
        self::DESCRIPTION,
    ];

    public const MAP = [
        self::NAME        => '昵称',
        self::AVATAR      => '头像',
        self::DESCRIPTION => '个性签名',
    ];
}
