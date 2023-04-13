<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * 站内通知 事件类型.
 *
 * Class NoticeEvent
 */
class NoticeEvent extends BaseEnum
{
    public const FEED_LIKE = 1;
    public const FEED_COMMENT = 2;
    public const COMMENT_REPLY = 3;
    public const COMMENT_LIKE = 4;
    public const USER_FOLLOW = 5;
    public const COMMENT_FORBIDDEN = 6;
    public const SYSTEM_NOTICE = 7;
    public const FEED_FORBIDDEN = 8;
    public const FEED_IMAGE_FORBIDDEN = 9;
    public const USER_INFO_RESET = 10;

    // 所有事件
    public const LIST = [
        self::FEED_LIKE,
        self::FEED_COMMENT,
        self::COMMENT_REPLY,
        self::COMMENT_LIKE,
        self::USER_FOLLOW,
        self::COMMENT_FORBIDDEN,
        self::SYSTEM_NOTICE,
        self::FEED_FORBIDDEN,
        self::FEED_IMAGE_FORBIDDEN,
        self::USER_INFO_RESET,
    ];

    // 互动事件
    public const INTERACTIVE = [
        self::FEED_LIKE,
        self::FEED_COMMENT,
        self::COMMENT_REPLY,
        self::COMMENT_LIKE,
        self::USER_FOLLOW,
    ];

    public const MAP = [
        self::FEED_LIKE            => '动态点赞',
        self::FEED_COMMENT         => '动态评论',
        self::COMMENT_REPLY        => '评论回复',
        self::COMMENT_LIKE         => '评论点赞',
        self::USER_FOLLOW          => '用户关注',
        self::COMMENT_FORBIDDEN    => '评论被屏蔽',
        self::SYSTEM_NOTICE        => '系统通知',
        self::FEED_FORBIDDEN       => '动态屏蔽',
        self::FEED_IMAGE_FORBIDDEN => '动态图片屏蔽',
        self::USER_INFO_RESET      => '用户信息重置',
    ];
}
