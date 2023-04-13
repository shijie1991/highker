<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class UserRanking extends BaseEnum
{
    public const LIKE = 'like';
    public const COMMENT = 'comment';
    public const ADD_FEED = 'add_feed';
    public const ADD_BOX = 'add_box';
    public const GET_BOX = 'get_box';
    public const USER = 'user';

    public const LIST = [
        self::LIKE,
        self::COMMENT,
        self::ADD_FEED,
        self::ADD_BOX,
        self::GET_BOX,
        self::USER,
    ];

    public const MAP = [
        self::USER => [
            'slug'  => self::USER,
            'name'  => '用户活跃榜',
            'en'    => 'User Rank',
            'cover' => '',
            'tips'  => '本周活跃度',
            'score' => 0,
        ],
        self::ADD_FEED => [
            'slug'  => self::ADD_FEED,
            'name'  => '发帖榜',
            'en'    => 'Posting Rank',
            'cover' => '',
            'tips'  => '本周发布动态',
            'score' => 4,
        ],
        self::COMMENT => [
            'slug'  => self::COMMENT,
            'name'  => '评论榜',
            'en'    => 'Comment Rank',
            'cover' => '',
            'tips'  => '本周评论',
            'score' => 2,
        ],
        self::LIKE => [
            'slug'  => self::LIKE,
            'name'  => '点赞榜',
            'en'    => 'Like Rank',
            'cover' => '',
            'tips'  => '本周点赞',
            'score' => 1,
        ],
        self::ADD_BOX => [
            'slug'  => self::ADD_BOX,
            'name'  => '放盲盒榜',
            'en'    => 'Add Box Rank',
            'cover' => '',
            'tips'  => '本周放盲盒',
            'score' => 2,
        ],
        self::GET_BOX => [
            'slug'  => self::GET_BOX,
            'name'  => '拆盲盒榜',
            'en'    => 'Get Box Rank',
            'cover' => '',
            'tips'  => '本周拆盲盒',
            'score' => 1,
        ],
    ];
}
