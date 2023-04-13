<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class VipProduct extends BaseEnum
{
    public const MONTH = 1;
    public const QUARTER = 2;
    public const YEAR = 3;

    public const LIST = [
        self::MONTH,
        self::QUARTER,
        self::YEAR,
    ];

    public const MAP = [
        self::MONTH => [
            'slug'  => self::MONTH,
            'moon'  => 1,
            'price' => 28,
            // 'price'    => 1,
            'discount' => '',
            'name'     => '1个月',
            'day'      => '每天仅 0.9 元',
        ],
        self::QUARTER => [
            'slug'     => self::QUARTER,
            'moon'     => 3,
            'price'    => 68,
            'discount' => '8.1折',
            'name'     => '3个月',
            'day'      => '每天仅 0.8 元',
        ],
        self::YEAR => [
            'slug'     => self::YEAR,
            'moon'     => 12,
            'price'    => 198,
            'discount' => '5.9折',
            'name'     => '12个月',
            'day'      => '每天仅 0.6 元',
        ],
    ];
}
