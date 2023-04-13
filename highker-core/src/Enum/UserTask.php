<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

/**
 * Class UserTask.
 */
class UserTask extends BaseEnum
{
    public const ADD_BOX = 1;
    public const GET_BOX = 2;
    public const DAILY_LOGIN = 3;
    public const VIEW_FEED = 4;
    public const FOLLOW_USER = 5;
    public const COMMENT = 6;
    public const LIKE_FEED = 7;
    public const CREATE_FEED = 8;
    public const COMPLETE_USER_INFO = 9;
    public const VIEW_VIDEO_AD = 10;
    public const FOLLOW_TOPIC = 11;
    public const PRIVATE_MESSAGE = 12;

    public const LIST = [
        self::ADD_BOX,
        self::GET_BOX,
        self::DAILY_LOGIN,
        self::VIEW_FEED,
        self::FOLLOW_USER,
        self::COMMENT,
        self::LIKE_FEED,
        self::CREATE_FEED,
        self::COMPLETE_USER_INFO,
        self::VIEW_VIDEO_AD,
        self::FOLLOW_TOPIC,
        self::PRIVATE_MESSAGE,
    ];

    public const MAP = [
        self::DAILY_LOGIN => [
            'slug'           => self::DAILY_LOGIN,
            'name'           => '每日登陆',
            'score'          => 1,
            'exp'            => 10,
            'must_count'     => 1,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => false,
        ],
        // self::VIEW_VIDEO_AD => [
        //     'slug'           => self::VIEW_VIDEO_AD,
        //     'name'           => '查看视频广告',
        //     'score'          => 2,
        //     'exp'            => 20,
        //     'must_count'     => 1,
        //     'complete_count' => 0,
        //     'finish'         => false,
        //     'once'           => false,
        // ],
        self::ADD_BOX => [
            'slug'           => self::ADD_BOX,
            'name'           => '放 3 个盲盒',
            'score'          => 2,
            'exp'            => 20,
            'must_count'     => 3,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => false,
        ],
        self::GET_BOX => [
            'slug'           => self::GET_BOX,
            'name'           => '拆 3 个盲盒',
            'score'          => 1,
            'exp'            => 10,
            'must_count'     => 3,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => false,
        ],
        self::VIEW_FEED => [
            'slug'           => self::VIEW_FEED,
            'name'           => '查看 3 条动态',
            'score'          => 1,
            'exp'            => 10,
            'must_count'     => 3,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => false,
        ],
        self::COMMENT => [
            'slug'           => self::COMMENT,
            'name'           => '完成 3 次评论',
            'score'          => 2,
            'exp'            => 20,
            'must_count'     => 3,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => false,
        ],
        self::LIKE_FEED => [
            'slug'           => self::LIKE_FEED,
            'name'           => '完成 5 次点赞',
            'score'          => 1,
            'exp'            => 10,
            'must_count'     => 5,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => false,
        ],
        self::CREATE_FEED => [
            'slug'           => self::CREATE_FEED,
            'name'           => '发布 1 条动态',
            'score'          => 2,
            'exp'            => 20,
            'must_count'     => 1,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => false,
        ],
        self::COMPLETE_USER_INFO => [
            'slug'           => self::COMPLETE_USER_INFO,
            'name'           => '完善用户信息',
            'score'          => 2,
            'exp'            => 20,
            'must_count'     => 1,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => true,
        ],
        // self::FOLLOW_TOPIC => [
        //     'slug'           => self::FOLLOW_TOPIC,
        //     'name'           => '关注话题',
        //     'score'          => 2,
        //     'exp'            => 20,
        //     'must_count'     => 1,
        //     'complete_count' => 0,
        //     'finish'         => false,
        //     'once'           => true,
        // ],
        self::FOLLOW_USER => [
            'slug'           => self::FOLLOW_USER,
            'name'           => '关注用户',
            'score'          => 2,
            'exp'            => 20,
            'must_count'     => 1,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => true,
        ],
        self::PRIVATE_MESSAGE => [
            'slug'           => self::PRIVATE_MESSAGE,
            'name'           => '向 TA 发送一条私信',
            'score'          => 2,
            'exp'            => 20,
            'must_count'     => 1,
            'complete_count' => 0,
            'finish'         => false,
            'once'           => true,
        ],
    ];
}
