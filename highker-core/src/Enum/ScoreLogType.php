<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Class ScoreLogType.
 */
class ScoreLogType extends BaseEnum
{
    public const INCREMENT = 1;
    public const DECREMENT = 2;

    public const LIST = [
        self::INCREMENT,
        self::DECREMENT,
    ];

    public const MAP = [
        self::INCREMENT => '增加',
        self::DECREMENT => '消耗',
    ];
}
