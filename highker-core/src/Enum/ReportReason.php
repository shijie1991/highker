<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Class ReportReason.
 */
class ReportReason extends BaseEnum
{
    public const POLITICAL = 1;
    public const PORN = 2;
    public const ABUSE = 3;
    public const BLOODY = 4;
    public const AD = 5;
    public const BILK = 6;
    public const ILLEGAL = 7;
    public const OTHER = 8;

    public const LIST = [
        self::POLITICAL,
        self::PORN,
        self::ABUSE,
        self::BLOODY,
        self::AD,
        self::BILK,
        self::ILLEGAL,
        self::OTHER,
    ];

    public const MAP = [
        self::POLITICAL => '政治敏感',
        self::PORN      => '低俗色情',
        self::ABUSE     => '攻击谩骂',
        self::BLOODY    => '血腥暴力',
        self::AD        => '广告引流',
        self::BILK      => '涉嫌诈骗',
        self::ILLEGAL   => '违法信息',
        self::OTHER     => '其他',
    ];
}
