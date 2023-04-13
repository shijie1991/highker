<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class UserEmotion extends BaseEnum
{
    public const SOLO = 1;
    public const SINGLE = 2;
    public const WAIT = 3;
    public const TEASE = 4;
    public const SOMEONE = 5;
    public const LOVE = 6;
    public const HARD = 7;

    public const LIST = [
        self::SOLO,
        self::SINGLE,
        self::WAIT,
        self::TEASE,
        self::SOMEONE,
        self::LOVE,
        self::HARD,
    ];

    public const MAP = [
        self::SOLO    => '母胎SOLO',
        self::SINGLE  => '今日单身',
        self::WAIT    => '等TA出现',
        self::TEASE   => '单身可撩',
        self::SOMEONE => '心里有人',
        self::LOVE    => '恋爱中',
        self::HARD    => '一言难尽',
    ];
}
