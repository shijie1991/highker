<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Class UserPrivilege.
 */
class UserPrivilege extends BaseEnum
{
    public const ADD_BOX = 1;
    public const GET_BOX = 2;
    public const PRIVATE_MESSAGE = 3;
    public const SCORE = 4;
    public const COMMENT_STICKERS = 5;
    public const COMMENT_IMAGES = 6;
    public const LEVEL_STYLE = 7;
    public const MORE = 8;

    public const LIST = [
        self::ADD_BOX,
        self::GET_BOX,
        self::PRIVATE_MESSAGE,
        self::SCORE,
        self::COMMENT_STICKERS,
        self::COMMENT_IMAGES,
        self::LEVEL_STYLE,
        self::MORE,
    ];

    public const MAP = [
        self::ADD_BOX          => '放盲盒次数',
        self::GET_BOX          => '拆盲盒次数',
        self::PRIVATE_MESSAGE  => '私信次数',
        self::SCORE            => '金币',
        self::COMMENT_STICKERS => '表情评论',
        self::COMMENT_IMAGES   => '图片评论',
        self::LEVEL_STYLE      => '新的等级样式',
        self::MORE             => '更多特权',
    ];
}
